<?php
session_start();
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>Claver Control Center</title>
    
    <style>
        .input-wrapper {
            width: 500px;
            margin:50px auto 0px auto;
        }

        /* Set a style for all buttons */
        button {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        /* Add a hover effect for buttons */
        button:hover {
            opacity: 0.8;
        }


        /* Center the avatar image inside this container */
        .imgcontainer {
            text-align: center;
            margin: 24px 0 12px 0;
        }

        /* Avatar image */
        img.avatar {
            width: 100%;
        }


        /* The "Forgot password" text */
        span.psw {
            float: right;
            padding-top: 16px;
        }

    </style>
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="/assets/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
</head>
<body>




    <form class="login100-form validate-form" action="actions/authenticate.php" method="post">

    <div class="imgcontainer">
        <img src="/assets/images/claver-control-center.png" alt="Avatar" class="avatar">
    </div>
    <div class="input-wrapper">
    <?php if (isset($_SESSION['login-error'])){ echo "<div class=\"login-error-msg\">".$_SESSION['login-error']."</div>"; } ?>
    <div class="wrap-input100 validate-input" data-validate="User name is required">
        <input class="input100" type="text" name="username" id="username" placeholder="User Name" required>
        <span class="focus-input100"></span>
        <span class="symbol-input100">
            <i class="fa fa-user" aria-hidden="true"></i>
        </span>
    </div>

    <div class="wrap-input100 validate-input" data-validate="Password is required">
        <input class="input100" type="password" name="password" placeholder="Password" id="password" required>
        <span class="focus-input100"></span>
        <span class="symbol-input100">
            <i class="fa fa-lock" aria-hidden="true"></i>
        </span>
    </div>


    
    <div class="container-login100-form-btn">
        <button class="login100-form-btn" type="submit"> Login </button>
    </div>

    <div style="text-align: center; margin-bottom: 20px;">
        <div>
            <a class="txt2" href="#"> Do you have an invitation code? </a>
        </div>
        <div>
            <span class="txt1"> Forgot </span>
            <a class="txt2" href="#"> Username / Password? </a>
        </div>
    </div>





    </div>




    </form>




<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/main.js"></script>

<div style="height:100%"></div>

<footer class="footer">Copyright ©2020, McColm Robotics</footer>
<!-- <?php include 'actions/utility/helpers.php'; show_viewport_size(); ?> -->
</body>
</html>

<?php
if(isset($_SESSION['login-error'])){
    unset($_SESSION['login-error']);
}
?>
