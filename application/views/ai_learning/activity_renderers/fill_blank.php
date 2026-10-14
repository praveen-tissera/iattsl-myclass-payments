<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$mark = function ($id) use ($result) {
    if ($result === NULL || !isset($result['results'][$id])) { return ''; }
    return $result['results'][$id] ? ' <span class="badge badge-success">Correct</span>' : ' <span class="badge badge-danger">Try again</span>';
};
?>
<?php foreach ($content['items'] as $number => $item): ?>
    <div class="form-group">
        <label for="b_<?php echo html_escape($item['id']); ?>">
            <?php echo ($number + 1) . '. ' . html_escape($item['before']); ?>
            <input class="form-control d-inline-block" style="width: 12rem;" type="text" maxlength="80" autocomplete="off" id="b_<?php echo html_escape($item['id']); ?>" name="answers[<?php echo html_escape($item['id']); ?>]" value="<?php echo html_escape(isset($answers[$item['id']]) ? $answers[$item['id']] : ''); ?>">
            <?php echo html_escape($item['after']) . $mark($item['id']); ?>
        </label>
    </div>
<?php endforeach; ?>
