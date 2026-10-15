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

    // ---------------------------------------------------------------------------------------------
    // Embedded visuals (PDF and DOCX only). Everything is held in memory: no image is written to disk.
    // ---------------------------------------------------------------------------------------------

    private function visual_limit($key, $default)
    {
        $CI = &get_instance();
        $value = $CI->config->item($key);
        return is_numeric($value) && (float) $value > 0 ? (float) $value : (float) $default;
    }

    // Returns array('visuals' => list, 'skipped' => counts, 'notes' => list, 'vector_pages' => list).
    // Each visual: source ('pdf_page'|'docx_image'), page (PDF page or NULL), ordinal, bytes, mime, width, height, context, key.
    public function extract_visuals($file_path, $extension)
    {
        if (!is_string($file_path) || !is_file($file_path) || !is_readable($file_path)) {
            throw new RuntimeException('The stored material file is unavailable.');
        }
        $size = filesize($file_path);
        if ($size === FALSE || $size < 1 || $size > $this->maximum_file_size) {
            throw new RuntimeException('The material exceeds the configured processing size limit.');
        }
        if (!function_exists('getimagesizefromstring')) {
            throw new RuntimeException('Image inspection support (GD) is unavailable on this server.');
        }

        $extension = strtolower((string) $extension);
        $state = array(
            'candidates' => array(),
            'skipped' => array('small' => 0, 'duplicate' => 0, 'unsupported' => 0, 'corrupt' => 0),
            'seen' => array(),
            'notes' => array(),
            'vector_pages' => array()
        );
        if ($extension === 'pdf') {
            $this->collect_pdf_visuals($file_path, $state);
        } elseif ($extension === 'docx') {
            $this->collect_docx_visuals($file_path, $state);
        } else {
            throw new RuntimeException('Embedded visuals are only supported for PDF and DOCX files.');
        }

        return array(
            'visuals' => $state['candidates'],
            'skipped' => $state['skipped'],
            'notes' => $state['notes'],
            'vector_pages' => $state['vector_pages']
        );
    }

    // Cuts one page out of a PDF as a standalone one-page PDF (pure PHP, via FPDI) so drawn diagrams and tables can be
    // read by the vision model without any server-side renderer. Returns the PDF bytes, or a string error key via array.
    // Result: array('ok' => TRUE, 'bytes' => string) or array('ok' => FALSE, 'reason' => 'unavailable'|'unsupported'|'failed').
    public function extract_pdf_page($file_path, $page)
    {
        $page = (int) $page;
        if ($page < 1 || !is_string($file_path) || !is_file($file_path)) {
            return array('ok' => FALSE, 'reason' => 'failed');
        }
        $autoload_path = FCPATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (!is_file($autoload_path)) {
            return array('ok' => FALSE, 'reason' => 'unavailable');
        }
        require_once $autoload_path;
        if (!class_exists('setasign\Fpdi\Fpdi')) {
            return array('ok' => FALSE, 'reason' => 'unavailable');
        }
        try {
            $pdf = new setasign\Fpdi\Fpdi();
            $count = $pdf->setSourceFile($file_path);
            if ($page > $count) {
                return array('ok' => FALSE, 'reason' => 'failed');
            }
            $template = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($template);
            $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
            $pdf->useTemplate($template);
            $bytes = $pdf->Output('S');
        } catch (setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
            return array('ok' => FALSE, 'reason' => 'unsupported');
        } catch (Throwable $e) {
            return array('ok' => FALSE, 'reason' => 'failed');
        }
        $limit = (int) $this->visual_limit('ai_visual_max_page_pdf_bytes', 3145728);
        if (!is_string($bytes) || $bytes === '' || strlen($bytes) > $limit) {
            return array('ok' => FALSE, 'reason' => 'failed');
        }

        return array('ok' => TRUE, 'bytes' => $bytes);
    }
    private function add_visual_candidate(&$state, $source, $page, $bytes, $context)
    {
        $image = $this->normalise_image($bytes);
        if (is_string($image)) {
            $state['skipped'][$image]++;
            return;
        }
        $key = sha1($image['bytes']);
        if (isset($state['seen'][$key])) {
            $state['skipped']['duplicate']++;
            return;
        }
        $state['seen'][$key] = TRUE;
        $state['candidates'][] = array(
            'key' => $key,
            'source' => $source,
            'page' => $page,
            'ordinal' => count($state['candidates']) + 1,
            'bytes' => $image['bytes'],
            'mime' => $image['mime'],
            'width' => $image['width'],
            'height' => $image['height'],
            'context' => $context
        );
    }

    // Returns array(bytes, mime, width, height) or one of the reason strings: small, unsupported, corrupt.
    private function normalise_image($bytes)
    {
        $min_dimension = (int) $this->visual_limit('ai_visual_min_dimension', 100);
        $max_dimension = (int) $this->visual_limit('ai_visual_max_dimension', 1600);
        $max_bytes = (int) $this->visual_limit('ai_visual_max_image_bytes', 4194304);
        $max_pixels = (int) $this->visual_limit('ai_visual_max_pixels', 16000000);

        if (!is_string($bytes) || $bytes === '' || strlen($bytes) > 40 * 1048576) {
            return 'corrupt';
        }
        $info = @getimagesizefromstring($bytes);
        if (!is_array($info) || (int) $info[0] < 1 || (int) $info[1] < 1) {
            return 'corrupt';
        }
        $width = (int) $info[0];
        $height = (int) $info[1];
        if ($width * $height > $max_pixels) {
            return 'unsupported';
        }
        $type = (int) $info[2];
        if (!in_array($type, array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_BMP), TRUE) ||
            ($type === IMAGETYPE_JPEG && isset($info['channels']) && (int) $info['channels'] === 4)) {
            return 'unsupported';
        }
        // Decorative content: tiny icons, bullets, rules and thin banners carry no teaching content.
        if (min($width, $height) < $min_dimension || $width * $height < $min_dimension * $min_dimension * 2 ||
            max($width, $height) / max(1, min($width, $height)) > 8) {
            return 'small';
        }

        $keep = in_array($type, array(IMAGETYPE_JPEG, IMAGETYPE_PNG), TRUE) &&
            max($width, $height) <= $max_dimension && strlen($bytes) <= $max_bytes;
        if ($keep) {
            return array('bytes' => $bytes, 'mime' => $type === IMAGETYPE_PNG ? 'image/png' : 'image/jpeg', 'width' => $width, 'height' => $height);
        }

        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            return 'unsupported';
        }
        $source = @imagecreatefromstring($bytes);
        if ($source === FALSE) {
            return 'corrupt';
        }
        $scale = min(1, $max_dimension / max($width, $height));
        $new_width = max(1, (int) round($width * $scale));
        $new_height = max(1, (int) round($height * $scale));
        $canvas = imagecreatetruecolor($new_width, $new_height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        ob_start();
        imagejpeg($canvas, NULL, 85);
        $encoded = ob_get_clean();
        imagedestroy($source);
        imagedestroy($canvas);
        if (!is_string($encoded) || $encoded === '' || strlen($encoded) > $max_bytes) {
            return 'unsupported';
        }

        return array('bytes' => $encoded, 'mime' => 'image/jpeg', 'width' => $new_width, 'height' => $new_height);
    }

    // ---- PDF ----

    private function collect_pdf_visuals($file_path, &$state)
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

        $page_numbers = array();
        foreach ($pages as $index => $page) {
            $number = $index + 1;
            $before = count($state['candidates']);
            try {
                foreach ($this->pdf_page_images($page, $this->pdf_page_content($page)) as $image_object) {
                    $bytes = $this->pdf_image_bytes($image_object);
                    if (!is_string($bytes)) {
                        $state['skipped']['unsupported']++;
                        continue;
                    }
                    $this->add_visual_candidate($state, 'pdf_page', $number, $bytes, '');
                }
            } catch (Throwable $exception) {
                $state['skipped']['corrupt']++;
            }
            if (count($state['candidates']) === $before) {
                try {
                    if ($this->pdf_page_has_drawn_graphics($page)) {
                        $state['vector_pages'][] = $number;
                    }
                } catch (Throwable $exception) {
                    // Heuristic only; an unreadable content stream is not an error for the document.
                }
            }
            $page_numbers[] = $number;
        }
    }

    // Image XObjects drawn from the page's resources, which may be inherited from the parent page tree. One Form level is followed.
    private function pdf_page_images($page, $page_content)
    {
        $resources = NULL;
        $node = $page;
        for ($depth = 0; $depth < 6 && $node !== NULL; $depth++) {
            $candidate = $node->get('Resources');
            if (is_object($candidate) && method_exists($candidate, 'has')) {
                $resources = $candidate;
                break;
            }
            $parent = $node->get('Parent');
            $node = is_object($parent) && method_exists($parent, 'get') ? $parent : NULL;
        }

        $used = array();
        if (preg_match_all('/\/([^\s\/\[\]()<>]+)\s+Do(?=\s|$)/', $page_content, $matches)) {
            $used = array_flip($matches[1]);
        }

        return $this->pdf_resource_images($resources, 0, $used);
    }

    private function pdf_resource_images($resources, $depth, $used)
    {
        $images = array();
        if (!is_object($resources) || !method_exists($resources, 'has') || !$resources->has('XObject')) {
            return $images;
        }
        $xobject_table = $resources->get('XObject');
        if ($xobject_table instanceof \Smalot\PdfParser\Header) {
            $elements = $xobject_table->getElements();
        } elseif (is_object($xobject_table) && method_exists($xobject_table, 'getHeader')) {
            $elements = $xobject_table->getHeader()->getElements();
        } else {
            return $images;
        }
        foreach ($elements as $name => $element) {
            if (!isset($used[$name])) {
                continue;
            }
            if ($element instanceof \Smalot\PdfParser\XObject\Image) {
                $images[] = $element;
            } elseif ($element instanceof \Smalot\PdfParser\XObject\Form && $depth < 1) {
                $form_used = array();
                if (preg_match_all('/\/([^\s\/\[\]()<>]+)\s+Do(?=\s|$)/', (string) $element->getContent(), $form_matches)) {
                    $form_used = array_flip($form_matches[1]);
                }
                $images = array_merge($images, $this->pdf_resource_images($element->get('Resources'), $depth + 1, $form_used));
            }
        }

        return $images;
    }

    // Rebuilds a standalone image from a PDF image stream. Returns NULL for formats that cannot be rebuilt safely.
    private function pdf_image_bytes($image)
    {
        $content = $image->getContent();
        if (!is_string($content) || $content === '') {
            return NULL;
        }
        if (substr($content, 0, 2) === "\xFF\xD8") {
            return $content;
        }

        $details = $image->getHeader()->getDetails();
        $filter = isset($details['Filter']) ? json_encode($details['Filter']) : '';
        if (preg_match('/DCT|JPX|CCITT|JBIG2/', (string) $filter) === 1) {
            return NULL;
        }
        $color_space = isset($details['ColorSpace']) ? json_encode($details['ColorSpace']) : '';
        if (preg_match('/Indexed|Lab|Separation|DeviceN|CMYK/', (string) $color_space) === 1 ||
            (isset($details['ImageMask']) && $details['ImageMask'] === 'true')) {
            return NULL;
        }
        $width = isset($details['Width']) ? (int) $details['Width'] : 0;
        $height = isset($details['Height']) ? (int) $details['Height'] : 0;
        $bits = isset($details['BitsPerComponent']) ? (int) $details['BitsPerComponent'] : 8;
        if ($width < 1 || $height < 1 || $bits !== 8 || $width * $height > (int) $this->visual_limit('ai_visual_max_pixels', 16000000)) {
            return NULL;
        }

        $predictor = 1;
        if (isset($details['DecodeParms']) && is_array($details['DecodeParms']) && isset($details['DecodeParms']['Predictor'])) {
            $predictor = (int) $details['DecodeParms']['Predictor'];
        }
        $length = strlen($content);
        if ($predictor >= 10) {
            $row_bytes = intdiv($length, $height) - 1;
            $colors = $width > 0 ? intdiv($row_bytes, $width) : 0;
            if ($length !== ($row_bytes + 1) * $height || $row_bytes !== $width * $colors) {
                return NULL;
            }
            $raw = $content;
        } elseif ($predictor === 1) {
            $colors = intdiv($length, $width * $height);
            if ($length !== $width * $height * $colors) {
                return NULL;
            }
            $raw = '';
            $row_bytes = $width * $colors;
            for ($row = 0; $row < $height; $row++) {
                $raw .= "\0" . substr($content, $row * $row_bytes, $row_bytes);
            }
        } else {
            return NULL;
        }
        if ($colors !== 1 && $colors !== 3) {
            return NULL;
        }

        return $this->build_png($width, $height, $colors === 3 ? 2 : 0, $raw);
    }

    private function build_png($width, $height, $color_type, $filtered_rows)
    {
        $chunk = function ($type, $data) {
            return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
        };

        return "\x89PNG\r\n\x1a\n" .
            $chunk('IHDR', pack('NNCCCCC', $width, $height, 8, $color_type, 0, 0, 0)) .
            $chunk('IDAT', gzcompress($filtered_rows, 6)) .
            $chunk('IEND', '');
    }

    // Heuristic: many path-painting operators on a page without an image suggest a drawn (vector) diagram or table borders.
    private function pdf_page_content($page)
    {
        $contents = $page->get('Contents');
        $streams = array();
        if (is_object($contents) && method_exists($contents, 'getContent')) {
            $content = $contents->getContent();
            if (is_string($content)) {
                $streams[] = $content;
            } elseif (is_array($content)) {
                foreach ($content as $part) {
                    if (is_object($part) && method_exists($part, 'getContent') && is_string($part->getContent())) {
                        $streams[] = $part->getContent();
                    }
                }
            }
        }
        return implode("\n", $streams);
    }

    private function pdf_page_has_drawn_graphics($page)
    {
        $operations = preg_match_all('/(?:^|\s)(?:S|s|f|F|f\*|B|b|B\*|b\*)(?=\s|$)/', $this->pdf_page_content($page));

        return $operations >= (int) $this->visual_limit('ai_visual_vector_operation_threshold', 8);
    }

    // ---- DOCX ----

    private function collect_docx_visuals($file_path, &$state)
    {
        if (!class_exists('ZipArchive') || !class_exists('DOMDocument') || !class_exists('DOMXPath')) {
            throw new RuntimeException('Required DOCX processing support is unavailable.');
        }
        $archive = new ZipArchive();
        if ($archive->open($file_path, ZIPARCHIVE::CHECKCONS) !== TRUE) {
            throw new RuntimeException('The DOCX document is not a valid Office package.');
        }

        $media = array();
        try {
            $total = 0;
            for ($index = 0; $index < $archive->numFiles; $index++) {
                $entry = $archive->statIndex($index);
                if (!is_array($entry) || !isset($entry['size']) ||
                    $entry['size'] > $this->maximum_docx_uncompressed_size - $total) {
                    throw new RuntimeException('The DOCX package exceeds the configured extraction limit.');
                }
                $total += (int) $entry['size'];
            }
            $xml = $archive->getFromName('word/document.xml');
            $relationships_xml = $archive->getFromName('word/_rels/document.xml.rels');
            if (!is_string($xml) || $xml === '' || stripos($xml, '<!DOCTYPE') !== FALSE) {
                throw new RuntimeException('The DOCX document content is invalid.');
            }
            $relationships = $this->docx_image_relationships($relationships_xml);
            // Read only the media files that the body actually references.
            $referenced = array();
            $document = $this->load_docx_xml($xml);
            $paragraph_rows = $this->docx_paragraph_rows($document, $relationships);
            foreach ($paragraph_rows as $row) {
                foreach ($row['targets'] as $target) {
                    $referenced[$target] = TRUE;
                }
            }
            $per_image_limit = (int) $this->visual_limit('ai_visual_max_image_bytes', 4194304) * 4;
            foreach (array_keys($referenced) as $target) {
                $stat = $archive->statName($target);
                if (!is_array($stat) || !isset($stat['size']) || $stat['size'] < 1 || $stat['size'] > $per_image_limit) {
                    $media[$target] = FALSE;
                    continue;
                }
                $media[$target] = $archive->getFromName($target);
            }
        } finally {
            $archive->close();
        }

        $heading = '';
        $last_text = '';
        $count = count($paragraph_rows);
        foreach ($paragraph_rows as $position => $row) {
            if ($row['is_heading'] && $row['text'] !== '') {
                $heading = $row['text'];
            }
            if (!empty($row['targets'])) {
                $caption = $row['text'];
                if ($caption === '') {
                    for ($ahead = 1; $ahead <= 2 && $position + $ahead < $count; $ahead++) {
                        $next = $paragraph_rows[$position + $ahead];
                        if ($next['text'] !== '' && ($next['is_caption'] || preg_match('/^(figure|fig\.?|diagram|chart|table|illustration|image)\b/i', $next['text']) === 1)) {
                            $caption = $next['text'];
                            break;
                        }
                    }
                }
                $parts = array();
                if ($heading !== '') {
                    $parts[] = 'Section heading: ' . $this->shorten($heading, 200);
                }
                if ($caption !== '') {
                    $parts[] = 'Caption or nearby text: ' . $this->shorten($caption, 300);
                }
                if ($row['alt'] !== '') {
                    $parts[] = 'Author alt text: ' . $this->shorten($row['alt'], 250);
                }
                if ($last_text !== '' && $caption === '') {
                    $parts[] = 'Preceding text: ' . $this->shorten($last_text, 250);
                }
                foreach ($row['targets'] as $target) {
                    if (!isset($media[$target]) || !is_string($media[$target])) {
                        $state['skipped']['corrupt']++;
                        continue;
                    }
                    $this->add_visual_candidate($state, 'docx_image', NULL, $media[$target], implode('; ', $parts));
                }
            }
            if ($row['text'] !== '' && !$row['is_heading']) {
                $last_text = $row['text'];
            }
        }
    }

    private function load_docx_xml($xml)
    {
        $previous = libxml_use_internal_errors(TRUE);
        $document = new DOMDocument();
        $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (!$loaded || !empty($errors)) {
            throw new RuntimeException('The DOCX document contains invalid XML.');
        }

        return $document;
    }

    // Maps relationship ids to safe media paths inside the package (word/...). External and unsafe targets are dropped.
    private function docx_image_relationships($relationships_xml)
    {
        $map = array();
        if (!is_string($relationships_xml) || $relationships_xml === '' || stripos($relationships_xml, '<!DOCTYPE') !== FALSE) {
            return $map;
        }
        $document = $this->load_docx_xml($relationships_xml);
        foreach ($document->getElementsByTagName('Relationship') as $relationship) {
            $type = (string) $relationship->getAttribute('Type');
            $target = (string) $relationship->getAttribute('Target');
            if (substr($type, -6) !== '/image' || strtolower((string) $relationship->getAttribute('TargetMode')) === 'external' ||
                $target === '' || strpos($target, '\\') !== FALSE || strpos($target, ':') !== FALSE) {
                continue;
            }
            $path = $target[0] === '/' ? ltrim($target, '/') : 'word/' . $target;
            $segments = array();
            foreach (explode('/', $path) as $segment) {
                if ($segment === '' || $segment === '.') {
                    continue;
                }
                if ($segment === '..') {
                    array_pop($segments);
                    continue;
                }
                $segments[] = $segment;
            }
            $path = implode('/', $segments);
            if (strpos($path, 'word/') === 0 && strpos($path, '..') === FALSE) {
                $map[(string) $relationship->getAttribute('Id')] = $path;
            }
        }

        return $map;
    }

    private function docx_paragraph_rows($document, $relationships)
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $xpath->registerNamespace('wp', 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing');
        $xpath->registerNamespace('v', 'urn:schemas-microsoft-com:vml');
        $xpath->registerNamespace('mc', 'http://schemas.openxmlformats.org/markup-compatibility/2006');
        $paragraphs = $xpath->query('//w:body//w:p');
        if ($paragraphs === FALSE) {
            throw new RuntimeException('The DOCX text could not be read.');
        }

        $rows = array();
        foreach ($paragraphs as $paragraph) {
            $text = '';
            $text_nodes = $xpath->query('.//w:t', $paragraph);
            if ($text_nodes !== FALSE) {
                foreach ($text_nodes as $node) {
                    $text .= $node->textContent;
                }
            }
            $style = $xpath->evaluate('string(w:pPr/w:pStyle/@w:val)', $paragraph);
            $outline = $xpath->evaluate('count(w:pPr/w:outlineLvl)', $paragraph);
            $targets = array();
            $blips = $xpath->query('.//a:blip[@r:embed] | .//v:imagedata[@r:id][not(ancestor::mc:Fallback)]', $paragraph);
            if ($blips !== FALSE) {
                foreach ($blips as $blip) {
                    $id = $blip->hasAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'embed')
                        ? $blip->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'embed')
                        : $blip->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
                    if (isset($relationships[$id]) && !in_array($relationships[$id], $targets, TRUE)) {
                        $targets[] = $relationships[$id];
                    }
                }
            }
            $alt = '';
            $properties = $xpath->query('.//wp:docPr', $paragraph);
            if ($properties !== FALSE && $properties->length > 0) {
                $alt = trim($properties->item(0)->getAttribute('descr') . ' ' . $properties->item(0)->getAttribute('title'));
            }
            $rows[] = array(
                'text' => trim(preg_replace('/\s+/u', ' ', $text)),
                'is_heading' => preg_match('/^(heading\s?\d*|title|subtitle)$/i', (string) $style) === 1 || $outline > 0,
                'is_caption' => strtolower((string) $style) === 'caption',
                'targets' => $targets,
                'alt' => $alt
            );
        }

        return $rows;
    }

    private function shorten($text, $limit)
    {
        $text = trim((string) $text);
        if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $limit) {
            return mb_substr($text, 0, $limit - 1, 'UTF-8') . '…';
        }

        return $text;
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
