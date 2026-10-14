<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$mark = function ($id) use ($result) {
    if ($result === NULL || !isset($result['results'][$id])) { return ''; }
    return $result['results'][$id] ? ' <span class="badge badge-success">Correct</span>' : ' <span class="badge badge-danger">Try again</span>';
};
?>
<?php foreach ($content['left'] as $number => $left): ?>
    <div class="form-group row">
        <label class="col-md-5 col-form-label" for="m_<?php echo html_escape($left['id']); ?>"><?php echo ($number + 1) . '. ' . html_escape($left['text']) . $mark($left['id']); ?></label>
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
