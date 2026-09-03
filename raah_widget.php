<style>
    #raah-widget { position: fixed; bottom: 30px; right: 30px; z-index: 9999; font-family: 'Segoe UI', Roboto, sans-serif; }
    
    #raah-btn { 
        background: linear-gradient(135deg, #008cff 0%, #0056b3 100%); 
        color: white; width: 60px; height: 60px; border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 26px; cursor: pointer; 
        box-shadow: 0 8px 25px rgba(0,140,255,0.4); 
        border: none; transition: all 0.3s ease; 
    }
    #raah-btn:hover { transform: scale(1.1) rotate(5deg); }

    #raah-chat-box { 
        display: none; width: 350px; height: 480px; 
        background: white; border-radius: 16px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.15); 
        flex-direction: column; overflow: hidden; 
        margin-bottom: 20px; border: 1px solid #e2e8f0; 
        animation: slideUp 0.4s ease;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .raah-header { 
        background: #008cff; color: white; padding: 18px; 
        font-weight: 700; display: flex; justify-content: space-between; align-items: center; 
    }
    
    .raah-body { 
        flex: 1; padding: 15px; overflow-y: auto; 
        background: #f8fafc; display: flex; flex-direction: column; gap: 12px; 
        scroll-behavior: smooth;
    }

    .raah-msg { 
        max-width: 85%; padding: 12px 16px; border-radius: 15px; 
        font-size: 14px; line-height: 1.5; position: relative;
    }
    
    .msg-bot { background: white; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 2px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .msg-user { background: #008cff; color: white; align-self: flex-end; border-bottom-right-radius: 2px; }

    .raah-input-area { display: flex; border-top: 1px solid #e2e8f0; padding: 15px; background: #fff; gap: 10px; }
    .raah-input-area input { 
        flex: 1; border: 1px solid #e2e8f0; outline: none; 
        padding: 10px 15px; border-radius: 25px; font-size: 14px; transition: 0.2s;
    }
    .raah-input-area input:focus { border-color: #008cff; background: #fdfdfd; }
    
    .raah-input-area button { 
        background: #008cff; border: none; color: white; 
        width: 38px; height: 38px; border-radius: 50%; 
        cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center;
    }
    .raah-input-area button:hover { background: #0056b3; }
</style>

<div id="raah-widget">
    <div id="raah-chat-box">
        <div class="raah-header">
            <span><i class="fas fa-robot"></i> Raah (Roamie AI)</span>
            <i class="fas fa-times" style="cursor: pointer; opacity: 0.8;" onclick="toggleRaah()"></i>
        </div>
        <div class="raah-body" id="raah-messages">
            <div class="raah-msg msg-bot">Namaste! 🙏 I'm Raah. How can I help you plan your journey with Roamie today?</div>
        </div>
        <div class="raah-input-area">
            <input type="text" id="raah-input" placeholder="Find Your Way..." onkeypress="if(event.key === 'Enter') sendRaahMessage()">
            <button onclick="sendRaahMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <button id="raah-btn" onclick="toggleRaah()"><i class="fas fa-comment-dots"></i></button>
</div>

<script>
    function toggleRaah() {
        const box = document.getElementById('raah-chat-box');
        box.style.display = (box.style.display === 'flex') ? 'none' : 'flex';
        if(box.style.display === 'flex') {
            document.getElementById('raah-input').focus();
        }
    }

    function sendRaahMessage() {
        const input = document.getElementById('raah-input');
        const message = input.value.trim();
        if(!message) return;

        const msgContainer = document.getElementById('raah-messages');
        
        // Append User Message
        msgContainer.innerHTML += `<div class="raah-msg msg-user">${message}</div>`;
        input.value = '';
        msgContainer.scrollTop = msgContainer.scrollHeight;

        // Show "typing" indicator or just wait for backend
        const formData = new FormData();
        formData.append('message', message);

        fetch('raah_bot.php', { method: 'POST', body: formData })
        .then(response => response.text())
        .then(data => {
            // Append Bot Response with a natural delay
            setTimeout(() => {
                msgContainer.innerHTML += `<div class="raah-msg msg-bot">${data}</div>`;
                msgContainer.scrollTop = msgContainer.scrollHeight;
            }, 600);
        })
        .catch(err => {
            console.error('Bot Error:', err);
            msgContainer.innerHTML += `<div class="raah-msg msg-bot" style="color:red;">Sorry, I'm having trouble connecting. Please try again later!</div>`;
        });
    }
</script>