<?php

function dd($item) {
    echo "<pre style='background: #222;color: #54ff00;padding: 20px;'>";
    print_r($item);
    echo "</pre>";
    die();
}