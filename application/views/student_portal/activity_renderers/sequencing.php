<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$total = count($content['items']);
?>
<p class="text-muted">Put the steps in the correct order. Use the Move up and Move down buttons, or pick a position number for each step.</p>
<ol class="list-unstyled" id="sequence-list">
    <?php foreach ($content['items'] as $index => $item): ?>
        <li class="card card-body mb-2 sequence-item">
            <div class="d-flex flex-wrap align-items-center">
                <span class="flex-grow-1 mr-2"><?php echo html_escape($item['text']); ?></span>
                <label class="mb-0 mr-2" for="s_<?php echo html_escape($item['id']); ?>">Position</label>
                <select class="form-control form-control-sm sequence-position mr-2" style="width: 5rem;" id="s_<?php echo html_escape($item['id']); ?>" name="answers[<?php echo html_escape($item['id']); ?>]">
                    <?php for ($position = 1; $position <= $total; $position++): ?>
                        <?php $chosen = isset($answers[$item['id']]) ? (string) $answers[$item['id']] : (string) ($index + 1); ?>
                        <option value="<?php echo $position; ?>" <?php echo $chosen === (string) $position ? 'selected' : ''; ?>><?php echo $position; ?></option>
                    <?php endfor; ?>
                </select>
                <span class="sequence-buttons"></span>
            </div>
        </li>
    <?php endforeach; ?>
</ol>
