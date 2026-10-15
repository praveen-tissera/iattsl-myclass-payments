<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Draws a sample sheet attached to a quiz question. $sheet is validated server-side when the activity is generated.
$is_sheet = $sheet['style'] === 'spreadsheet';
$letter = function ($index) {
    $name = '';
    for ($n = $index + 1; $n > 0; $n = intdiv($n - 1, 26)) {
        $name = chr(65 + (($n - 1) % 26)) . $name;
    }
    return $name;
};
$columns = count($sheet['rows'][0]);
?>
<div class="ai-sheet mb-3">
    <?php if ($sheet['title'] !== ''): ?><div class="font-weight-bold small mb-1"><?php echo html_escape($sheet['title']); ?></div><?php endif; ?>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0" style="width:auto;background:#fff;font-size:.9rem;">
            <?php if ($is_sheet): ?>
                <thead>
                    <tr style="background:#e9ecef;text-align:center;">
                        <th style="width:2.2rem;"></th>
                        <?php for ($c = 0; $c < $columns; $c++): ?><th><?php echo html_escape($letter($c)); ?></th><?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sheet['rows'] as $r => $row): ?>
                        <tr>
                            <th style="background:#e9ecef;text-align:center;"><?php echo $r + 1; ?></th>
                            <?php foreach ($row as $cell): ?><td><?php echo html_escape($cell); ?></td><?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php else: ?>
                <thead>
                    <tr style="background:#e9ecef;"><?php foreach ($sheet['rows'][0] as $cell): ?><th><?php echo html_escape($cell); ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($sheet['rows'], 1) as $row): ?>
                        <tr><?php foreach ($row as $cell): ?><td><?php echo html_escape($cell); ?></td><?php endforeach; ?></tr>
                    <?php endforeach; ?>
                </tbody>
            <?php endif; ?>
        </table>
    </div>
</div>