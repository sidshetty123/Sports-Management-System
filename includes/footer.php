<!-- Modern Footer -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<br>
<footer class="modern-footer">
    <div class="footer-content">
        <div class="logo-section">
            <i class="fas fa-heartbeat pulse-icon"></i>
            <span class="college-name">Vignan's</span>
        </div>
        <p class="footer-text">Sports Management System</p>
        <div class="creator-section">
            <i class="fas fa-code"></i>
            <span>Created By Siddhardha & Sunny of 3rd BCA</span>
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="social-icons">
            <i class="fas fa-basketball-ball"></i>
            <i class="fas fa-volleyball-ball"></i>
            <i class="fas fa-running"></i>
            <i class="fas fa-table-tennis"></i>
        </div>
        <p class="copyright">&copy; <?php echo date("Y"); ?> All Rights Reserved</p>
    </div>
</footer>

<style>
    .modern-footer {
        background: linear-gradient(135deg, #4C3F91, #9145B6);
        color: white;
        text-align: center;
        padding: 20px;
        position: sticky;
        width: 100%;
        box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
    }

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 10px;
    }

    .logo-section {
        margin-bottom: 10px;
    }

    .pulse-icon {
        font-size: 2.5rem;
        color: #FF6B6B;
        margin-right: 10px;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .college-name {
        font-size: 1.8rem;
        font-weight: bold;
        background: linear-gradient(45deg, #FF6B6B, #FFE66D);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .footer-text {
        font-size: 1.2rem;
        margin: 10px 0;
        color: rgba(255, 255, 255, 0.9);
    }

    .creator-section {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin: 15px 0;
        padding: 10px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 30px;
        width: fit-content;
        margin-left: auto;
        margin-right: auto;
    }

    .creator-section i {
        color: #FFE66D;
    }

    .social-icons {
        margin: 15px 0;
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .social-icons i {
        font-size: 1.5rem;
        transition: all 0.3s ease;
        color: rgba(255, 255, 255, 0.8);
    }

    .social-icons i:hover {
        color: #FFE66D;
        transform: translateY(-3px);
    }

    .copyright {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 10px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .modern-footer {
            padding: 15px;
        }

        .college-name {
            font-size: 1.5rem;
        }

        .footer-text {
            font-size: 1rem;
        }

        .creator-section {
            flex-direction: column;
            gap: 5px;
        }

        .social-icons {
            gap: 15px;
        }
    }
</style>
