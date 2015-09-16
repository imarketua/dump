<?php
session_start();

if (isset($_SESSION['mobile'])) {
  unset($_SESSION['mobile']);
} else {
  $_SESSION['mobile'] = true;
}

header('Location: '.$_SESSION['url']);
