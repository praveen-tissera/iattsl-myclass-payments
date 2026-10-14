<?php
defined('BASEPATH') OR exit('No direct script access allowed');
foreach ($content['items'] as $number => $item):
?>
<div class="ai-step card card-body mb-3">
    <label class="mb-0" for="b_<?php echo html_escape($item['id']); ?>">
        <span class="sr-only">Sentence </span><?php echo ($number + 1) . '. ' . html_escape($item['before']); ?>
        <input class="form-control d-inline-block mx-1" style="width: 12rem; max-width: 100%;" type="text" maxlength="80" autocomplete="off" id="b_<?php echo html_escape($item['id']); ?>" name="answers[<?php echo html_escape($item['id']); ?>]" value="<?php echo html_escape(isset($answers[$item['id']]) && is_string($answers[$item['id']]) ? $answers[$item['id']] : ''); ?>" aria-label="Missing word for sentence <?php echo $number + 1; ?>">
        <?php echo html_escape($item['after']); ?>
    </label>
</div>
<?php endforeach; ?>
