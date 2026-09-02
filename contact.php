<?php session_start(); include 'includes/header.php'; ?>

<div style="max-width: 900px; margin: 60px auto; padding: 20px; font-family: 'Segoe UI', sans-serif;">
    <div style="text-align: center; margin-bottom: 50px;">
        <h1 style="color: #0a223d; font-size: 36px;">Contact Our Team</h1>
        <p style="color: #64748b;">We'll get back to you as soon as possible.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 50px;">
        <div style="background: #0a223d; color: white; padding: 40px; border-radius: 12px;">
            <h3 style="margin-bottom: 25px;">Get in Touch</h3>
            <p style="margin-bottom: 20px;"><i class="fas fa-envelope" style="color: #008cff;"></i> support@roamie.com</p>
            <p><i class="fas fa-map-marker-alt" style="color: #008cff;"></i> Surat, Gujarat, India</p>
        </div>

        <form id="contactForm" onsubmit="return validateContact(event)" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display:block; margin-bottom: 8px; font-weight: 600;">Full Name*</label>
                <input type="text" id="contact_name" placeholder="Your Name" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
            </div>
            <div>
                <label style="display:block; margin-bottom: 8px; font-weight: 600;">Email*</label>
                <input type="email" id="contact_email" placeholder="email@example.com" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;">
            </div>
            <div>
                <label style="display:block; margin-bottom: 8px; font-weight: 600;">Message*</label>
                <textarea id="contact_message" rows="5" placeholder="How can we help you?" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;"></textarea>
            </div>
            <button type="submit" style="background: #008cff; color: white; border: none; padding: 15px; border-radius: 8px; font-weight: 700; cursor: pointer;">Send Message</button>
        </form>
    </div>
</div>

<script>
function validateContact(event) {
    event.preventDefault();
    const name = document.getElementById('contact_name').value.trim();
    const email = document.getElementById('contact_email').value.trim();
    const message = document.getElementById('contact_message').value.trim();

    if (name === "" || email === "" || message === "") {
        alert("Please fill in all required fields.");
        return false;
    }
    
    alert("Thank you, " + name + "! Your message has been sent successfully.");
    document.getElementById('contactForm').reset();
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>