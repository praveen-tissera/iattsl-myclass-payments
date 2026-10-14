<?php
defined('BASEPATH') OR exit('No direct script access allowed');
foreach ($content['items'] as $number => $item):
    $name = 'answers[' . $item['id'] . ']';
?>
<fieldset class="ai-step card card-body mb-3">
    <legend class="h5"><span class="sr-only">Question </span><?php echo ($number + 1) . '. ' . html_escape($item['prompt']); ?></legend>
    <?php foreach ($item['options'] as $option): ?>
        <div class="custom-control custom-radio mb-2">
            <input class="custom-control-input" type="radio" id="o_<?php echo html_escape($option['id']); ?>" name="<?php echo html_escape($name); ?>" value="<?php echo html_escape($option['id']); ?>" <?php echo isset($answers[$item['id']]) && $answers[$item['id']] === $option['id'] ? 'checked' : ''; ?>>
            <label class="custom-control-label" for="o_<?php echo html_escape($option['id']); ?>"><?php echo html_escape($option['text']); ?></label>
        </div>
    <?php endforeach; ?>
</fieldset>
<?php endforeach; ?>
