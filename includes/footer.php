<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <script src="../assets/js/bootstrap.bundle.js"></script>
    <link rel="stylesheet" href="../assets/fontawesome-7.1.1/css/all.min.css" type="text/css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            padding-bottom: 70px;
        }

        .footer {
            background: #330099;
            color: #FFFFFF;
            min-height: 70px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            padding: 15px 20px;
        }

        .footer p {
            margin: 0;
            font-weight: bold;
            font-size: 16px;
            line-height: 1.6;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .footer-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .footer-separator {
            margin: 0 8px;
        }

        .footer i {
            font-size: 18px;
            color: white;
        }

        /* Tablet and smaller screens */
        @media (max-width: 768px) {
            body {
                padding-bottom: 90px;
            }

            .footer {
                min-height: 90px;
                padding: 12px 15px;
            }

            .footer p {
                font-size: 14px;
                gap: 6px;
            }

            .footer i {
                font-size: 16px;
            }

            .footer-separator {
                display: none;
            }
        }

        /* Mobile phones */
        @media (max-width: 480px) {
            body {
                padding-bottom: 110px;
            }

            .footer {
                min-height: 110px;
                padding: 10px 12px;
            }

            .footer p {
                font-size: 12px;
                gap: 5px;
                flex-direction: column;
            }

            .footer-item {
                gap: 4px;
            }

            .footer i {
                font-size: 14px;
            }
        }

        /* Very small phones */
        @media (max-width: 360px) {
            body {
                padding-bottom: 130px;
            }

            .footer {
                min-height: 130px;
                padding: 8px 10px;
            }

            .footer p {
                font-size: 11px;
            }

            .footer i {
                font-size: 13px;
            }
        }

        /* Landscape orientation on mobile */
        @media (max-height: 500px) and (orientation: landscape) {
            body {
                padding-bottom: 60px;
            }

            .footer {
                min-height: 60px;
                padding: 8px 10px;
            }

            .footer p {
                font-size: 11px;
                gap: 4px;
            }

            .footer i {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="footer">
        <p>
            <span class="footer-item">
                <i class="fas fa-envelope"></i>
                <span>sittilyani@gmail.com</span>
            </span>
            <span class="footer-item">
                <i class="fab fa-whatsapp"></i>
                <span>+254 722 42 77 21</span>
            </span>
            <span class="footer-separator">||</span>
            <span class="footer-item">
                <span>&#9743;</span>
                <span>+254 722 42 77 21</span>
            </span>
            <span class="footer-item">
                <span>&copy; <?php echo date('Y');?></span>
            </span>
        </p>
    </div>
</body>
</html>