<?php
if (opcache_reset()) {
    echo "OPcache cleared OK";
} else {
    echo "opcache_reset failed or disabled";
}
