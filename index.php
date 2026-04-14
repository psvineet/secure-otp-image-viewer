<?php
if(session_status()!==PHP_SESSION_ACTIVE) session_start();

date_default_timezone_set("Asia/Kolkata");

/* ===== CONFIG ===== */
$PASSWORD="1234";
$LOCK=__DIR__."/lock.txt";
$LOGIN_LOG = __DIR__."/login_attempts.log";

$IMG_BASE="psim/";
$IMAGES=["1.jpg","2.jpg","3.jpg","4.jpg","5.jpg","6.jpg","7.jpg","8.jpg","9.jpg","10.jpg","11.jpg","12.jpg","13.jpg","14.jpg","15.jpg","16.jpg","17.jpg","18.jpg","19.jpg","20.jpg"];

$SESSION_TTL=40;
/* ================== */


/* ===== LOG FUNCTION ===== */
function log_login_event($type,$note=""){
 global $LOGIN_LOG;

 $ip = $_SERVER['REMOTE_ADDR'] ?? "IP";
 $ua = $_SERVER['HTTP_USER_AGENT'] ?? "Unknown";

 $line = "[".date("Y-m-d H:i:s")."] $type | IP=$ip | UA=".substr($ua,0,80)." | NOTE=$note\n";
 file_put_contents($LOGIN_LOG,$line,FILE_APPEND | LOCK_EX);
}


/* ===== REALTIME EXPIRE LOG ENDPOINT ===== */
if(isset($_POST['__log_expire'])){
 if(empty($_SESSION['expire_logged'])){
  log_login_event("EXPIRED","Session expired realtime");
  $_SESSION['expire_logged']=1;
 }
 unset($_SESSION['viewer_auth'],$_SESSION['viewer_time']);
 exit;
}


/* ===== SMART ROUTER REDIRECT ===== */
function smart_home_redirect(){
 if(isset($_GET['id']) || isset($_GET['router']) || isset($_GET['sig'])) return "?id=0";
 return "/";
}


/* ===== SESSION EXPIRE FALLBACK ===== */
if(isset($_SESSION['viewer_auth'],$_SESSION['viewer_time'])){
 if(time()-$_SESSION['viewer_time']>$SESSION_TTL){

  if(empty($_SESSION['expire_logged'])){
   log_login_event("EXPIRED","Session expired fallback");
   $_SESSION['expire_logged']=1;
  }

  unset($_SESSION['viewer_auth'],$_SESSION['viewer_time']);

  $target=smart_home_redirect();

  if(!headers_sent()){
   header("Location: ".$target);
   exit;
  } else {
   echo "<script>location.href='".htmlspecialchars($target)."';</script>";
   exit;
  }
 }
}


/* ===== LOCK CHECK ===== */
function pass_used($f){
 return file_exists($f) && filesize($f)>0;
}


/* ===== LOGIN ===== */
if(isset($_POST['pass'])){

 if($_POST['pass']===$PASSWORD){

  if(pass_used($LOCK)){
   $error="Password already used";
   log_login_event("BLOCKED","Password reuse attempt");
  } else {

   file_put_contents(
    $LOCK,
    ($_SERVER['REMOTE_ADDR']??"IP")." | ".date("H:i:s")."\n",
    FILE_APPEND
   );

   $_SESSION['viewer_auth']=true;
   $_SESSION['viewer_time']=time();
   unset($_SESSION['expire_logged']);

   log_login_event("SUCCESS","Login success");
  }

 } else {
  $error="Invalid password";
  log_login_event("FAILED","Invalid password entered");
 }

}

$auth=$_SESSION['viewer_auth']??false;
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width,initial-scale=1">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">

<title>pflare | Vineet</title>

<style>
*{box-sizing:border-box;font-family:'Noto Sans',Arial}
body{margin:0;background:#f4f6f9;color:#1f2933}

.login{
max-width:420px;
margin:70px auto;
padding:35px;
background:#fff;
border-radius:14px;
box-shadow:0 10px 25px rgba(0,0,0,.08);
text-align:center;
}

.login p{
color:#6b7280;
font-size:14px;
line-height:1.6;
}

input,button{
width:100%;
padding:14px;
margin-top:18px;
border-radius:10px;
font-size:15px;
}

input{border:1px solid #d1d5db;background:#f9fafb}
button{border:none;background:#2563eb;color:#fff;font-weight:600}

.notice{
background:#fff7ed;
color:#9a3412;
padding:14px;
margin:20px;
border-radius:10px;
border:1px solid #fed7aa;
text-align:center;
}

.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
gap:20px;
padding:25px;
}

.wrap{
position:relative;
background:#fff;
padding:10px;
border-radius:14px;
box-shadow:0 8px 20px rgba(0,0,0,.06);
}

.wrap img{
width:100%;
border-radius:10px;
pointer-events:none;
user-select:none;
-webkit-user-drag:none;
}

.wm{
position:absolute;
bottom:12px;
right:12px;
font-size:11px;
background:rgba(0,0,0,.55);
color:#fff;
padding:4px 7px;
border-radius:6px;
}
</style>

<script>
// Basic browser protections
document.addEventListener("contextmenu",e=>e.preventDefault());
document.addEventListener("dragstart",e=>e.preventDefault());
document.addEventListener("selectstart",e=>e.preventDefault());
</script>

</head>
<body>

<?php if(!$auth): ?>

<div class="login">

<h3>Please enter One-Time Access Password</h3>

<p>
Access is monitored, logged, and time restricted for privacy protection.
</p>

<?php if(isset($error)) echo "<p style='color:#dc2626'>$error</p>"; ?>

<form method="POST">
<input type="password" name="pass" placeholder="Enter one time password" required>
<button>Access Secure Viewer</button>
</form>

</div>

<?php else: ?>

<div class="notice">
Please do not share these or discuss anyone about my psoriasis condition.
</div>

<div class="grid">

<?php
foreach($IMAGES as $img){
 $wm=($_SERVER['REMOTE_ADDR']??"IP")." | ".date("H:i:s");
 echo "<div class='wrap'>
 <img src='{$IMG_BASE}{$img}' loading='lazy'>
 <div class='wm'>$wm</div>
 </div>";
}
?>

</div>

<script>
const HOME_TARGET = "<?php echo htmlspecialchars(smart_home_redirect()); ?>";

function expireNow(){
 fetch(location.href,{
  method:"POST",
  headers:{
   "Content-Type":"application/x-www-form-urlencoded"
  },
  body:"__log_expire=1"
 }).finally(()=>{
  location.href = HOME_TARGET;
 });
}

// Tab hidden logout
document.addEventListener("visibilitychange",()=>{
 if(document.hidden) expireNow();
});

// Session timer
let s=<?php echo $SESSION_TTL-(time()-$_SESSION['viewer_time']);?>;
setInterval(()=>{
 if(--s<=0) expireNow();
},1000);
</script>

<?php endif; ?>

</body>
</html>
