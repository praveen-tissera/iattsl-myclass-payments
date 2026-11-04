<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Transaction date cell for a paid invoice. Administrators can edit the date; others see it read-only.
$stamp = empty($completion->created_at) ? 0 : strtotime($completion->created_at);
if (empty($is_admin) || empty($completion->ID)) {
    echo $stamp ? date('y-m-d h:i A', $stamp) : '-';
    return;
}
?>
<input type="datetime-local" class="form-control form-control-sm mb-1" name="transaction_date" value="<?php echo $stamp ? date('Y-m-d\TH:i', $stamp) : ''; ?>">
<input type="hidden" name="student_id" value="<?php echo html_escape($admission_number); ?>">
<input type="hidden" name="academicyear" value="<?php echo (int) $academic_year; ?>">
<button type="submit" class="btn btn-outline-secondary btn-sm" name="payment_id" value="<?php echo (int) $completion->ID; ?>" formaction="<?php echo site_url('online/updateTransactionDate'); ?>">Update date</button>
<button type="submit" class="btn btn-outline-danger btn-sm mt-1" name="payment_id" value="<?php echo (int) $completion->ID; ?>" formaction="<?php echo site_url('online/revertPayment'); ?>" onclick="return confirm('Mark this invoice as unpaid? The recorded payment will be permanently deleted.');">Mark unpaid</button>
