<?php include 'raah_widget.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roamie | Find Your Way</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Widget Styles */
        #raah-widget { position: fixed; bottom: 20px; right: 20px; z-index: 1000; font-family: 'Segoe UI', sans-serif; }
        #raah-btn { background: #008cff; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; cursor: pointer; box-shadow: 0 4px 15px rgba(0,140,255,0.4); border: none; transition: 0.3s; }
        #raah-btn:hover { transform: scale(1.1); }
        #raah-chat-box { display: none; width: 320px; height: 400px; background: white; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.2); flex-direction: column; overflow: hidden; margin-bottom: 15px; border: 1px solid #ddd; }
        .raah-header { background: #008cff; color: white; padding: 15px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .raah-body { flex: 1; padding: 15px; overflow-y: auto; background: #f9f9f9; display: flex; flex-direction: column; gap: 10px; }
        .raah-msg { max-width: 80%; padding: 10px; border-radius: 8px; font-size: 14px; line-height: 1.4; }
        .msg-bot { background: #eaf5ff; color: #000; align-self: flex-start; border-bottom-left-radius: 0; }
        .msg-user { background: #008cff; color: white; align-self: flex-end; border-bottom-right-radius: 0; }
        .raah-input-area { display: flex; border-top: 1px solid #ddd; padding: 10px; background: #fff; }
        .raah-input-area input { flex: 1; border: none; outline: none; padding: 8px; font-size: 14px; }
        .raah-input-area button { background: none; border: none; color: #008cff; font-size: 20px; cursor: pointer; }
        
        /* Footer link hover effect */
        footer a:hover { color: #008cff !important; }
    </style>
</head>
<body>

    <div id="raah-widget">
        <div id="raah-chat-box">
            <div class="raah-header">
                <span>🤖 Raah (Roamie Bot)</span>
                <i class="fas fa-times" style="cursor: pointer;" onclick="toggleRaah()"></i>
            </div>
            <div class="raah-body" id="raah-messages">
                <div class="raah-msg msg-bot">Hi! I'm Raah. Got questions about traveling in India? Ask away and Find Your Way!</div>
            </div>
            <div class="raah-input-area">
                <input type="text" id="raah-input" placeholder="Find Your Way..." onkeypress="if(event.key === 'Enter') sendRaahMessage()">
                <button onclick="sendRaahMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
        <button id="raah-btn" onclick="toggleRaah()"><i class="fas fa-comment-dots"></i></button>
    </div>

    <footer style="background-color: #0a223d; color: #ffffff; padding: 60px 0 30px 0; margin-top: 50px; font-family: 'Segoe UI', sans-serif;">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; padding: 0 20px;">
            <div>
                <img src="assets/img/roamie_logo.png" alt="Roamie" style="height: 40px; margin-bottom: 20px; filter: brightness(0) invert(1);">
                <p style="color: #cbd5e1; line-height: 1.6; font-size: 14px;">Connecting travelers with authentic local experiences. Your journey, our passion.</p>
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <a href="#" style="color: #ffffff;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" style="color: #ffffff;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: #ffffff;"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <div>
                <h4 style="color: #008cff; margin-bottom: 20px; font-size: 16px;">About Roamie</h4>
                <ul style="list-style: none; padding: 0; font-size: 14px; line-height: 2.5;">
                    <li><a href="about_us.php" style="color: #cbd5e1; text-decoration: none;">About Us</a></li>
                    <li><a href="culture.php" style="color: #cbd5e1; text-decoration: none;">Our Culture</a></li>
                    <li><a href="careers.php" style="color: #cbd5e1; text-decoration: none;">Careers</a></li>
                    <li><a href="investors.php" style="color: #cbd5e1; text-decoration: none;">Investors</a></li>
                </ul>
            </div>
            <div>
                <h4 style="color: #008cff; margin-bottom: 20px; font-size: 16px;">Support</h4>
                <ul style="list-style: none; padding: 0; font-size: 14px; line-height: 2.5;">
                    <li><a href="help_center.php" style="color: #cbd5e1; text-decoration: none;">Help Center</a></li>
                    <li><a href="sfty_resrc_center.php" style="color: #cbd5e1; text-decoration: none;">Safety Resource Center</a></li>
                    <li><a href="cancellation_opt.php" style="color: #cbd5e1; text-decoration: none;">Cancellation Options</a></li>
                    <li><a href="report_concern.php" style="color: #cbd5e1; text-decoration: none;">Report a Concern</a></li>
                </ul>
            </div>
            <div>
                <h4 style="color: #008cff; margin-bottom: 20px; font-size: 16px;">Contact Us</h4>
                <p style="color: #cbd5e1; font-size: 14px; margin-bottom: 10px;"><i class="fas fa-map-marker-alt"></i> Surat, Gujarat, India</p>
                <p style="color: #cbd5e1; font-size: 14px; margin-bottom: 10px;"><i class="fas fa-envelope"></i> support@roamie.com</p>
                <p style="color: #cbd5e1; font-size: 14px;"><i class="fas fa-phone-alt"></i> +91 12345 67890</p>
            </div>
        </div>
        <div style="max-width: 1200px; margin: 40px auto 0 auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); text-align: center; color: #94a3b8; font-size: 13px;">
            &copy; 2026 Roamie. All rights reserved.
        </div>
    </footer>

    <script>
        function toggleRaah() {
            var box = document.getElementById('raah-chat-box');
            box.style.display = (box.style.display === 'flex') ? 'none' : 'flex';
        }

        function sendRaahMessage() {
            var input = document.getElementById('raah-input');
            var message = input.value.trim();
            if(!message) return;

            var msgContainer = document.getElementById('raah-messages');
            
            // Append User Message
            msgContainer.innerHTML += `<div class="raah-msg msg-user">${message}</div>`;
            input.value = '';
            msgContainer.scrollTop = msgContainer.scrollHeight;

            // Send to Backend
            var formData = new FormData();
            formData.append('message', message);

            fetch('raah_bot.php', { method: 'POST', body: formData })
            .then(response => response.text())
            .then(data => {
                setTimeout(() => {
                    msgContainer.innerHTML += `<div class="raah-msg msg-bot">${data}</div>`;
                    msgContainer.scrollTop = msgContainer.scrollHeight;
                }, 500);
            });
        }
    </script>
</body>
</html>