<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<p class="text-muted">Choose the best match for each item. Each choice can be used once.</p>
<?php foreach ($content['left'] as $number => $left): ?>
    <div class="form-group row align-items-center">
        <label class="col-md-5 mb-1" for="m_<?php echo html_escape($left['id']); ?>"><?php echo ($number + 1) . '. ' . html_escape($left['text']); ?></label>
        <div class="col-md-7">
            <select class="form-control" id="m_<?php echo html_escape($left['id']); ?>" name="answers[<?php echo html_escape($left['id']); ?>]">
                <option value="">Choose a match</option>
                <?php foreach ($content['right'] as $right): ?>
                    <option value="<?php echo html_escape($right['id']); ?>" <?php echo isset($answers[$left['id']]) && $answers[$left['id']] === $right['id'] ? 'selected' : ''; ?>><?php echo html_escape($right['text']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
<?php endforeach; ?>
