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
    <?php if (!empty($item['sheet']) && ($number === 0 || empty($content['items'][$number - 1]['sheet']) || $content['items'][$number - 1]['sheet'] != $item['sheet'])) { $this->load->view('ai_learning/activity_renderers/_sheet', array('sheet' => $item['sheet'])); } ?>
    <?php if (!empty($visual_base) && isset($item['image']['file']) && preg_match('/^[a-f0-9]{32}\.png$/', $item['image']['file'])): ?>
            <p class="text-center mb-3"><img src="<?php echo html_escape($visual_base . substr($item['image']['file'], 0, 32)); ?>" alt="<?php echo html_escape(isset($item['image']['alt']) ? $item['image']['alt'] : 'Question picture'); ?>" class="img-fluid border rounded" style="max-height:420px"></p>
        <?php endif; ?>
        <?php foreach ($item['options'] as $option): ?>
            <div class="form-check">
                <input class="form-check-input" type="radio" id="o_<?php echo html_escape($option['id']); ?>" name="answers[<?php echo html_escape($item['id']); ?>]" value="<?php echo html_escape($option['id']); ?>" <?php echo isset($answers[$item['id']]) && $answers[$item['id']] === $option['id'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="o_<?php echo html_escape($option['id']); ?>"><?php echo html_escape($option['text']); ?></label>
            </div>
        <?php endforeach; ?>
    </fieldset>
<?php endforeach; ?>
