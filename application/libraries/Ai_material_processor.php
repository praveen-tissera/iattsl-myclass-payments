<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_material_processor
{
    private $maximum_file_size;
    private $maximum_extracted_characters;
    private $maximum_docx_uncompressed_size;
    private $maximum_pdf_pages;

    public function __construct()
    {
        $CI = &get_instance();
        $CI->config->load('ai');

        $maximum_file_size_mb = (int) $CI->config->item('ai_material_max_processing_file_size_mb');
        $maximum_extracted_characters = (int) $CI->config->item('ai_material_max_extracted_characters');
        $maximum_docx_uncompressed_mb = (int) $CI->config->item('ai_material_max_docx_uncompressed_mb');
        $maximum_pdf_pages = (int) $CI->config->item('ai_material_max_pdf_pages');

        if ($maximum_file_size_mb < 1 || $maximum_extracted_characters < 1 ||
            $maximum_docx_uncompressed_mb < 1 || $maximum_pdf_pages < 1) {
            throw new RuntimeException('AI material processing limits are not configured correctly.');
        }

        $this->maximum_file_size = $maximum_file_size_mb * 1024 * 1024;
        $this->maximum_extracted_characters = $maximum_extracted_characters;
        $this->maximum_docx_uncompressed_size = $maximum_docx_uncompressed_mb * 1024 * 1024;
        $this->maximum_pdf_pages = $maximum_pdf_pages;
    }

    public function process($file_path, $extension)
    {
        if (!is_string($file_path) || !is_file($file_path) || !is_readable($file_path)) {
            throw new RuntimeException('The stored material file is unavailable.');
        }

        $file_size = filesize($file_path);
        if ($file_size === FALSE || $file_size < 1 || $file_size > $this->maximum_file_size) {
            throw new RuntimeException('The material exceeds the configured processing size limit.');
        }

        $extension = strtolower((string) $extension);
        if ($extension === 'pdf') {
            return $this->process_pdf($file_path);
        }
        if ($extension === 'docx') {
            return $this->process_docx($file_path);
        }
        if ($extension === 'txt') {
            return $this->process_text_file($file_path);
        }
        if (in_array($extension, array('jpg', 'jpeg', 'png'), TRUE)) {
            return $this->unsupported(
                'Image uploaded — content extraction is not implemented. The original file is still available.'
            );
        }
        if (in_array($extension, array('doc', 'ppt', 'pptx'), TRUE)) {
            return $this->unsupported(
                'Text extraction is not yet supported for this file format. The original file is still available.'
            );
        }

        return $this->unsupported(
            'Text extraction is not supported for this file format. The original file is still available.'
        );
    }

    private function process_pdf($file_path)
    {
        $autoload_path = FCPATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (!is_file($autoload_path)) {
            throw new RuntimeException('The PDF extraction library could not be loaded.');
        }
        require_once $autoload_path;

        if (!class_exists('Smalot\\PdfParser\\Parser')) {
            throw new RuntimeException('The PDF extraction library is unavailable.');
        }

        $parser = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($file_path);
        $pages = $document->getPages();
        if (count($pages) > $this->maximum_pdf_pages) {
            throw new RuntimeException('The PDF exceeds the configured page limit.');
        }

        $page_text = array();
        $has_readable_text = FALSE;
        foreach ($pages as $index => $page) {
            $text = $this->clean_text($page->getText());
            if (trim($text) !== '') {
                $has_readable_text = TRUE;
            }
            $page_text[] = '[Page ' . ($index + 1) . ']' . "\n\n" . $text;
        }

        $text = implode("\n\n", $page_text);
        $this->validate_extracted_text($text);
        if (!$has_readable_text) {
            throw new RuntimeException('No readable text was found in the PDF.');
        }

        return array(
            'status' => 'processed',
            'extracted_text' => $text,
            'page_count' => count($pages),
            'character_count' => $this->character_count($text),
            'error' => NULL
        );
    }

    private function process_docx($file_path)
    {
        if (!class_exists('ZipArchive') || !class_exists('DOMDocument') || !class_exists('DOMXPath')) {
            throw new RuntimeException('Required DOCX processing support is unavailable.');
        }

        $archive = new ZipArchive();
        if ($archive->open($file_path, ZIPARCHIVE::CHECKCONS) !== TRUE) {
            throw new RuntimeException('The DOCX document is not a valid Office package.');
        }

        try {
            $total_uncompressed_size = 0;
            for ($index = 0; $index < $archive->numFiles; $index++) {
                $entry = $archive->statIndex($index);
                if (!is_array($entry) || !isset($entry['size']) ||
                    $entry['size'] > $this->maximum_docx_uncompressed_size - $total_uncompressed_size) {
                    throw new RuntimeException('The DOCX package exceeds the configured extraction limit.');
                }
                $total_uncompressed_size += (int) $entry['size'];
            }

            $content_types = $archive->locateName('[Content_Types].xml');
            $document_index = $archive->locateName('word/document.xml');
            if ($content_types === FALSE || $document_index === FALSE) {
                throw new RuntimeException('The DOCX package is missing required document content.');
            }

            $document_stat = $archive->statIndex($document_index);
            if (!is_array($document_stat) || !isset($document_stat['size']) ||
                $document_stat['size'] > $this->maximum_docx_uncompressed_size) {
                throw new RuntimeException('The DOCX document exceeds the configured extraction limit.');
            }

            $xml = $archive->getFromIndex($document_index);
            if (!is_string($xml) || $xml === '' || stripos($xml, '<!DOCTYPE') !== FALSE) {
                throw new RuntimeException('The DOCX document content is invalid.');
            }
        } finally {
            $archive->close();
        }

        $previous_error_mode = libxml_use_internal_errors(TRUE);
        $document = new DOMDocument();
        $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT);
        $xml_errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous_error_mode);
        unset($xml);

        if (!$loaded || !empty($xml_errors)) {
            throw new RuntimeException('The DOCX document contains invalid XML.');
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $paragraphs = $xpath->query('//w:body//w:p');
        if ($paragraphs === FALSE) {
            throw new RuntimeException('The DOCX text could not be read.');
        }

        $text_paragraphs = array();
        foreach ($paragraphs as $paragraph) {
            $parts = $xpath->query('.//w:t | .//w:tab | .//w:br | .//w:cr', $paragraph);
            if ($parts === FALSE) {
                throw new RuntimeException('The DOCX text could not be read.');
            }

            $line = '';
            foreach ($parts as $part) {
                if ($part->localName === 'tab') {
                    $line .= "\t";
                } elseif ($part->localName === 'br' || $part->localName === 'cr') {
                    $line .= "\n";
                } else {
                    $line .= $part->textContent;
                }
            }
            $text_paragraphs[] = $line;
        }

        $text = $this->clean_text(implode("\n", $text_paragraphs));
        $this->validate_extracted_text($text);
        if (trim($text) === '') {
            throw new RuntimeException('No readable text was found in the DOCX document.');
        }

        return array(
            'status' => 'processed',
            'extracted_text' => $text,
            'page_count' => NULL,
            'character_count' => $this->character_count($text),
            'error' => NULL
        );
    }

    private function process_text_file($file_path)
    {
        $text = file_get_contents($file_path);
        if (!is_string($text)) {
            throw new RuntimeException('The text file could not be read.');
        }

        if (substr($text, 0, 3) === "\xEF\xBB\xBF") {
            $text = substr($text, 3);
        } elseif (substr($text, 0, 2) === "\xFF\xFE") {
            $text = $this->convert_encoding(substr($text, 2), 'UTF-16LE');
        } elseif (substr($text, 0, 2) === "\xFE\xFF") {
            $text = $this->convert_encoding(substr($text, 2), 'UTF-16BE');
        } elseif (preg_match('//u', $text) !== 1) {
            $text = $this->convert_encoding($text, 'Windows-1252');
        }

        $text = $this->clean_text($text);
        $this->validate_extracted_text($text);
        if (trim($text) === '') {
            throw new RuntimeException('The text file contains no readable text.');
        }

        return array(
            'status' => 'processed',
            'extracted_text' => $text,
            'page_count' => NULL,
            'character_count' => $this->character_count($text),
            'error' => NULL
        );
    }

    private function convert_encoding($text, $source_encoding)
    {
        $converted = iconv($source_encoding, 'UTF-8', $text);
        if ($converted === FALSE) {
            throw new RuntimeException('The text encoding could not be converted to UTF-8.');
        }

        return $converted;
    }

    private function clean_text($text)
    {
        if (!is_string($text)) {
            throw new RuntimeException('Extracted content is not valid text.');
        }

        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        if ($cleaned === NULL) {
            throw new RuntimeException('Extracted content contains invalid text encoding.');
        }

        return $cleaned;
    }

    private function validate_extracted_text($text)
    {
        if ($this->character_count($text) > $this->maximum_extracted_characters) {
            throw new RuntimeException('The extracted content exceeds the configured text limit.');
        }
    }

    private function character_count($text)
    {
        return function_exists('mb_strlen')
            ? mb_strlen($text, 'UTF-8')
            : strlen($text);
    }

    private function unsupported($message)
    {
        return array(
            'status' => 'unsupported',
            'extracted_text' => NULL,
            'page_count' => NULL,
            'character_count' => 0,
            'error' => $message
        );
    }
}
