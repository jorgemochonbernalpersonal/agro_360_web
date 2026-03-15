<?php
if (opcache_reset()) {
    echo "OPcache cleared OK";
} else {
    echo "opcache_reset failed or disabled";
}
// Self-destruct attempt (may not work if no write permission)
@unlink(__FILE__);
