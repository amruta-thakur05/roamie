<?php session_start(); include 'includes/header.php'; ?>

<div style="font-family: 'Segoe UI', sans-serif;">
    <section style="background: #0a223d; color: white; padding: 100px 20px; text-align: center;">
        <h1 style="font-size: 48px; font-weight: 900;">Build the Future of Roaming</h1>
        <p style="opacity: 0.8; font-size: 20px; max-width: 600px; margin: 20px auto 0 auto;">Join our mission to put India's local heroes on the global map.</p>
    </section>

    <section style="max-width: 900px; margin: 80px auto; padding: 0 20px;">
        <h2 style="color: #0a223d; margin-bottom: 40px; text-align: center;">Open Positions</h2>
        
        <?php 
        $jobs = [
            ['title' => 'Senior Backend Developer', 'dept' => 'Engineering', 'loc' => 'Surat / Remote'],
            ['title' => 'Growth Marketing Manager', 'dept' => 'Marketing', 'loc' => 'Mumbai / Remote'],
            ['title' => 'Community Operations Lead', 'dept' => 'Support', 'loc' => 'Surat, Gujarat'],
            ['title' => 'UI/UX Designer', 'dept' => 'Product', 'loc' => 'Remote'],
            ['title' => 'Partner Success Executive', 'dept' => 'Sales', 'loc' => 'Goa / Delhi']
        ];
        foreach ($jobs as $job): ?>
            <div style="background: white; border: 1px solid #e2e8f0; padding: 25px 35px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; transition: 0.3s; cursor: pointer;" onmouseover="this.style.borderColor='#008cff'" onmouseout="this.style.borderColor='#e2e8f0'">
                <div>
                    <span style="font-size: 12px; font-weight: 800; color: #008cff; text-transform: uppercase;"><?php echo $job['dept']; ?></span>
                    <h3 style="margin: 5px 0; color: #0a223d;"><?php echo $job['title']; ?></h3>
                    <span style="color: #64748b; font-size: 14px;"><i class="fas fa-map-marker-alt"></i> <?php echo $job['loc']; ?></span>
                </div>
                <a href="contact.php" style="background: #0a223d; color: white; text-decoration: none; padding: 12px 25px; border-radius: 10px; font-weight: 700; font-size: 14px;">Apply</a>
            </div>
        <?php endforeach; ?>
    </section>
</div>

<?php include 'includes/footer.php'; ?>