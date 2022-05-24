<?php

//
//echo __FILE__;

/*
 * đồng bộ code và database nếu có
 */
include __DIR__ . '/Sync.php';

//
WGR_vendor_sync();

// nạp header + footer cho admin
include __DIR__ . '/Header.php';
include __DIR__ . '/Footer.php';