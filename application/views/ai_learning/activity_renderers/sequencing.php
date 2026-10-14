<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$mark = function ($id) use ($result) {
    if ($result === NULL || !isset($result['results'][$id])) { return ''; }
    return $result['results'][$id] ? ' <span class="badge badge-success">Correct</span>' : ' <span class="badge badge-danger">Try again</span>';
};
$total = count($content['items']);
?>
<p class="text-muted">Give each step a position number from 1 to <?php echo (int) $total; ?>.</p>
<?php foreach ($content['items'] as $item): ?>
    <div class="form-group row">
        <label class="col-md-9 col-form-label" for="s_<?php echo html_escape($item['id']); ?>"><?php echo html_escape($item['text']) . $mark($item['id']); ?></label>
        <div class="col-md-3">
            <select class="form-control" id="s_<?php echo html_escape($item['id']); ?>" name="answers[<?php echo html_escape($item['id']); ?>]">
                <option value="">Position</option>
                <?php for ($position = 1; $position <= $total; $position++): ?>
                    <option value="<?php echo $position; ?>" <?php echo isset($answers[$item['id']]) && $answers[$item['id']] === (string) $position ? 'selected' : ''; ?>><?php echo $position; ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
<?php endforeach; ?>
