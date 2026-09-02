<?php session_start(); include 'includes/header.php'; ?>

<div style="font-family: 'Segoe UI', sans-serif; color: #1e293b;">
    <section style="background: linear-gradient(rgba(10, 34, 61, 0.7), rgba(10, 34, 61, 0.7)), url('https://images.unsplash.com/photo-1524492707947-53f7c1822896?q=80&w=1400'); background-size: cover; background-position: center; padding: 120px 20px; text-align: center; color: white;">
        <h1 style="font-size: 52px; font-weight: 900; margin-bottom: 20px;">We Don't Just Book Travel.</h1>
        <p style="font-size: 22px; max-width: 850px; margin: 0 auto; opacity: 0.9; line-height: 1.6;">We build the bridge between curious souls and local hearts. Roamie is about the stories shared over a cup of chai, not just the room you sleep in.</p>
    </section>

    <section style="max-width: 1100px; margin: 100px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center;">
        <div>
            <h2 style="color: #0a223d; font-size: 36px; margin-bottom: 25px;">The Human Connection</h2>
            <p style="line-height: 1.9; font-size: 17px; color: #475569; margin-bottom: 20px;">Roamie was founded in Surat to solve a digital disconnect. While big platforms focused on algorithms, we focused on **Atithi Devo Bhava**—the ancient Indian tradition that "The Guest is God."</p>
            <p style="line-height: 1.9; font-size: 17px; color: #475569;">Our platform connects you directly with local micro-entrepreneurs. When you book a cab, you're meeting a father supporting a family. When you book a stay, you're entering a home, not just a building.</p>
        </div>
        <div style="position: relative;">
            <img src="uploads/indian_hos.jpg" style="width: 100%; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.15);" alt="Indian Hospitality">
        </div>
    </section>

    <section style="background: #f8fafc; padding: 80px 20px; text-align: center;">
        <h2 style="color: #0a223d; margin-bottom: 50px;">Our Connection Pillars</h2>
        <div style="max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
            <div style="background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <i class="fas fa-users" style="font-size: 40px; color: #008cff; margin-bottom: 20px;"></i>
                <h3 style="color: #0a223d;">Community First</h3>
                <p style="color: #64748b;">We empower local partners by giving them the tools to reach the world while keeping their heritage intact.</p>
            </div>
            <div style="background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <i class="fas fa-heart" style="font-size: 40px; color: #ef4444; margin-bottom: 20px;"></i>
                <h3 style="color: #0a223d;">Authentic Bond</h3>
                <p style="color: #64748b;">Every partner is verified. You don't just meet a service provider; you meet a local guide who knows the soul of the city.</p>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>