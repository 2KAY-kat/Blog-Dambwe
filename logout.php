<?php
require 'config/constants.php';
// destroy everything

session_destroy();
header('location: ' . ROOT_URL . 'loggedout.php');
die();