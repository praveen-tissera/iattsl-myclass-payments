<?php
defined('BASEPATH') OR exit('No direct script access allowed');
foreach ($content['items'] as $number => $item):
    $name = 'answers[' . $item['id'] . ']';
?>
<fieldset class="ai-step card card-body mb-3">
    <legend class="h5"><span class="sr-only">Question </span><?php echo ($number + 1) . '. ' . html_escape($item['prompt']); ?></legend>
    <?php if (!empty($item['sheet']) && ($number === 0 || empty($content['items'][$number - 1]['sheet']) || $content['items'][$number - 1]['sheet'] != $item['sheet'])) { $this->load->view('ai_learning/activity_renderers/_sheet', array('sheet' => $item['sheet'])); } ?>
    <?php if (!empty($visual_base) && isset($item['image']['file']) && preg_match('/^[a-f0-9]{32}\.png$/', $item['image']['file'])): ?>
        <p class="text-center mb-3"><img src="<?php echo html_escape($visual_base . substr($item['image']['file'], 0, 32)); ?>" alt="<?php echo html_escape(isset($item['image']['alt']) ? $item['image']['alt'] : 'Question picture'); ?>" class="img-fluid border rounded" style="max-height:420px"></p>
    <?php endif; ?>
    <?php foreach ($item['options'] as $option): ?>
        <div class="custom-control custom-radio mb-2">
            <input class="custom-control-input" type="radio" id="o_<?php echo html_escape($option['id']); ?>" name="<?php echo html_escape($name); ?>" value="<?php echo html_escape($option['id']); ?>" <?php echo isset($answers[$item['id']]) && $answers[$item['id']] === $option['id'] ? 'checked' : ''; ?>>
            <label class="custom-control-label" for="o_<?php echo html_escape($option['id']); ?>"><?php echo html_escape($option['text']); ?></label>
        </div>
    <?php endforeach; ?>
</fieldset>
<?php endforeach; ?>
