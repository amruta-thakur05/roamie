<?php session_start(); include 'includes/header.php'; ?>

<div style="max-width: 900px; margin: 80px auto; padding: 20px; font-family: 'Segoe UI', sans-serif;">
    <h1 style="color: #0a223d; font-size: 32px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 40px;">Booking Cancellation & Refunds</h1>

    <div style="display: grid; gap: 20px;">
        <div style="display: flex; gap: 20px; background: #dcfce7; padding: 30px; border-radius: 12px; border-left: 8px solid #10b981;">
            <i class="fas fa-calendar-check" style="font-size: 24px; color: #166534;"></i>
            <div>
                <h3 style="margin: 0; color: #166534;">Flexible Cancellation</h3>
                <p style="margin: 10px 0 0 0; color: #166534; opacity: 0.9;">Full refund if cancelled up to 24 hours before the check-in date/time.</p>
            </div>
        </div>

        <div style="display: flex; gap: 20px; background: #eaf5ff; padding: 30px; border-radius: 12px; border-left: 8px solid #008cff;">
            <i class="fas fa-clock" style="font-size: 24px; color: #008cff;"></i>
            <div>
                <h3 style="margin: 0; color: #0056b3;">Moderate Cancellation</h3>
                <p style="margin: 10px 0 0 0; color: #0056b3; opacity: 0.9;">Full refund if cancelled at least 5 days before check-in. 50% refund after that.</p>
            </div>
        </div>

        <div style="display: flex; gap: 20px; background: #fef2f2; padding: 30px; border-radius: 12px; border-left: 8px solid #ef4444;">
            <i class="fas fa-lock" style="font-size: 24px; color: #991b1b;"></i>
            <div>
                <h3 style="margin: 0; color: #991b1b;">Strict Cancellation</h3>
                <p style="margin: 10px 0 0 0; color: #991b1b; opacity: 0.9;">50% refund if cancelled at least 7 days before check-in. No refund after that.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>