<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Recorded By and Transaction Date cells for an unpaid invoice. Only administrators can pick the transaction date.
?>
<td>-</td>
<td>
<?php if (!empty($is_admin)): ?>
    <input type="datetime-local" class="form-control form-control-sm" name="transaction_date" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
<?php else: ?>
    <?php echo date('y-m-d h:i A', strtotime(empty($invoice->updated_at) ? $invoice->created_at : $invoice->updated_at)); ?>
<?php endif; ?>
</td>
