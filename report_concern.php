<?php session_start(); include 'includes/header.php'; ?>

<div style="max-width: 700px; margin: 80px auto; padding: 40px; background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; font-family: 'Segoe UI', sans-serif;">
    <div style="text-align: center; margin-bottom: 30px;">
        <i class="fas fa-exclamation-triangle" style="font-size: 50px; color: #ef4444; margin-bottom: 20px;"></i>
        <h1 style="color: #0a223d;">Report a Concern</h1>
    </div>

    <form id="reportForm" onsubmit="return validateReport(event)" style="display: flex; flex-direction: column; gap: 20px;">
        <div>
            <label style="font-weight: 700; color: #475569; display: block; margin-bottom: 8px;">Issue Category*</label>
            <select id="report_cat" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                <option value="">Select a category...</option>
                <option>Safety Issue</option>
                <option>Inaccurate Listing</option>
                <option>Payment Issue</option>
            </select>
        </div>

        <div>
            <label style="font-weight: 700; color: #475569; display: block; margin-bottom: 8px;">Detailed Description*</label>
            <textarea id="report_desc" rows="6" placeholder="Please provide at least 20 characters..." required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px;"></textarea>
        </div>

        <button type="submit" style="background: #ef4444; color: white; border: none; padding: 15px; border-radius: 8px; font-weight: 700; cursor: pointer;">Submit Report</button>
    </form>
</div>

<script>
function validateReport(event) {
    event.preventDefault();
    const category = document.getElementById('report_cat').value;
    const description = document.getElementById('report_desc').value.trim();

    if (category === "") {
        alert("Please select a category.");
        return false;
    }
    if (description.length < 20) {
        alert("Please provide a more detailed description (at least 20 characters).");
        return false;
    }

    alert("Report Submitted. Our Trust & Safety team will review this immediately.");
    document.getElementById('reportForm').reset();
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>