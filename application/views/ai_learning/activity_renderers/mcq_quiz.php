<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$mark = function ($id) use ($result) {
    if ($result === NULL || !isset($result['results'][$id])) { return ''; }
    return $result['results'][$id] ? ' <span class="badge badge-success">Correct</span>' : ' <span class="badge badge-danger">Try again</span>';
};
?>
<?php foreach ($content['items'] as $number => $item): ?>
    <fieldset class="mb-4">
        <legend class="h6"><?php echo ($number + 1) . '. ' . html_escape($item['prompt']) . $mark($item['id']); ?></legend>
        <?php foreach ($item['options'] as $option): ?>
            <div class="form-check">
                <input class="form-check-input" type="radio" id="o_<?php echo html_escape($option['id']); ?>" name="answers[<?php echo html_escape($item['id']); ?>]" value="<?php echo html_escape($option['id']); ?>" <?php echo isset($answers[$item['id']]) && $answers[$item['id']] === $option['id'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="o_<?php echo html_escape($option['id']); ?>"><?php echo html_escape($option['text']); ?></label>
            </div>
        <?php endforeach; ?>
    </fieldset>
<?php endforeach; ?>
