<?php session_start(); include 'includes/header.php'; ?>

<div style="background: #0a223d; padding: 80px 20px; text-align: center; color: white;">
    <h1 style="font-size: 42px;">How can we help?</h1>
    <p style="font-size: 18px; opacity: 0.8;">Search for answers or browse categories below</p>
</div>

<div style="max-width: 1000px; margin: -40px auto 80px auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px;">
    
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
        <i class="fas fa-suitcase-rolling" style="font-size: 30px; color: #008cff; margin-bottom: 20px;"></i>
        <h3 style="color: #0a223d;">For Travelers</h3>
        <ul style="list-style: none; padding: 0; line-height: 2.5; font-size: 15px;">
            <li><a href="#" style="color: #475569; text-decoration: none;">How to book a stay?</a></li>
            <li><a href="#" style="color: #475569; text-decoration: none;">Changing my trip dates</a></li>
            <li><a href="#" style="color: #475569; text-decoration: none;">Refund status</a></li>
        </ul>
    </div>

    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
        <i class="fas fa-handshake" style="font-size: 30px; color: #10b981; margin-bottom: 20px;"></i>
        <h3 style="color: #0a223d;">For Partners</h3>
        <ul style="list-style: none; padding: 0; line-height: 2.5; font-size: 15px;">
            <li><a href="#" style="color: #475569; text-decoration: none;">Listing your first service</a></li>
            <li><a href="#" style="color: #475569; text-decoration: none;">When do I get paid?</a></li>
            <li><a href="#" style="color: #475569; text-decoration: none;">Managing multiple bookings</a></li>
        </ul>
    </div>

    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
        <i class="fas fa-shield-alt" style="font-size: 30px; color: #ef4444; margin-bottom: 20px;"></i>
        <h3 style="color: #0a223d;">Safety & Trust</h3>
        <ul style="list-style: none; padding: 0; line-height: 2.5; font-size: 15px;">
            <li><a href="#" style="color: #475569; text-decoration: none;">Verified Partner program</a></li>
            <li><a href="#" style="color: #475569; text-decoration: none;">Reporting a safety issue</a></li>
            <li><a href="#" style="color: #475569; text-decoration: none;">Guest conduct rules</a></li>
        </ul>
    </div>
</div>

<div style="text-align: center; margin-bottom: 80px;">
    <p>Can't find what you're looking for?</p>
    <a href="contact.php" style="background: #008cff; color: white; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: 700;">Contact Support</a>
</div>

<?php include 'includes/footer.php'; ?>