<?php
/**
 * This page will act as a hook for advertorial sites to append first-party cookies
 * to the domains from LeadGen promos before passing on the form submission to IRIS
 */

// Set vars
$root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
$cookie = isset($_COOKIE['r']) && $_COOKIE['r'] ? $_COOKIE['r'] : '' ;
$ping = isset($_GET['ping']) && $_GET['ping'] ? true : false ;

// Return ajax request to advertorial to confirm server is up
if($ping) {
    echo true;
    exit();
}

// Assign form data for IRIS submission
$Action = isset($_POST['Action']) && $_POST['Action'] ? $_POST['Action'] : '' ;
$MultivariateId = isset($_POST['MultivariateId']) && $_POST['MultivariateId'] ? $_POST['MultivariateId'] : '' ;
$NotSaveSignup = isset($_POST['NotSaveSignup']) && $_POST['NotSaveSignup'] ? $_POST['NotSaveSignup'] : '' ;
$PubCode = isset($_POST['PubCode']) && $_POST['PubCode'] ? $_POST['PubCode'] : '' ;
$CoRegs = isset($_POST['CoRegs']) && $_POST['CoRegs'] ? $_POST['CoRegs'] : '' ;
$Email = isset($_POST['Email']) && $_POST['Email'] ? $_POST['Email'] : '' ;

// If any piece is missing, redirect to homepage
if('' == ($Action || $MultivariateId || $NotSaveSignup || $PubCode || $Email)) {
    header("HTTP/1.1 307 Temporary Redirect");
    header("Location: ".$root);
    exit();
}

// Set from available cookies
$pubcodes = array();
if($cookie) {
    $pubcodes = explode('_', $cookie);
}

// If the new pubcode is not in the exisiting array, add it
if(!in_array($PubCode, $pubcodes)) {
    $pubcodes[] = $PubCode;
    $new_cookie = implode( '_', $pubcodes );
    setcookie( 'r', $new_cookie, time()+60*60*24*30 );
}

// Build the IRIS one-click URL for js fallback
$oneclick_url = $Action . '?MultivariateId=' . $MultivariateId . '&oneClick=true&email=' . urlencode($Email);

?>

<!doctype html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex,nofollow">
        <title>Processing your request...</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                position: fixed;
                background-color: #f5f5f5;
            }
            main {
                padding: 2rem 1rem;
                width: 100%;
                max-width: 960px;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                box-sizing: border-box;
            }
            h1 {
                margin-bottom: 2rem;
                font-family: sans-serif;
                color: #999;
                text-align: center;
            }
            p {
                margin-top: 2rem;
                font-family: sans-serif;
                color: #999;
                text-align: center;
            }
            a {
                color: #1779ba;
                text-decoration: none;
            }
            a:hover {
                text-decoration: underline;
            }
            .processing {
                margin: 0 auto;
                width: 60px;
                height: 60px;
                overflow: hidden;
                position: relative;
                border-radius: 50%;
                border-top: 12px solid rgba(153, 153, 153, 0.2);
                border-right: 12px solid rgba(153, 153, 153, 0.2);
                border-bottom: 12px solid rgba(153, 153, 153, 0.2);
                border-left: 12px solid #999;
                transform: translateZ(0);
                animation: spinnerAnimation 1.1s infinite linear;
            }
            .processing:after {
                border-radius: 50%;
                width: 60px;
                height: 60px;
            }
            @keyframes spinnerAnimation {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }
        </style>
    </head>
    <body>
        <main>
            <h1>Processing your request...</h1>
            <div class="processing"></div>
            <form method="post" id="LeadGen" action="<?php echo $Action; ?>">
                <input name="MultivariateId" type="hidden" value="<?php echo $MultivariateId; ?>">
                <input name="NotSaveSignup" type="hidden" value="<?php echo $NotSaveSignup; ?>">
                <?php if($CoRegs) {
                    foreach ($CoRegs as $row) {
                        echo '<input name="CoRegs" type="hidden" value="' . $row . '">';
                    }
                } ?>
                <input name="Email" type="hidden" value="<?php echo $Email; ?>">
            </form>
            <p>If you are not automatically redirected in the next few seconds, <a href="<?php echo $oneclick_url; ?>">click here</a></p>
        </main>
        <script>window.onload = document.getElementById("LeadGen").submit();</script>
    </body>
</html>