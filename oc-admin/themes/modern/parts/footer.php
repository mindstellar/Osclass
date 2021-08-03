<?php if (!defined('OC_ADMIN')) { exit('Direct access is not allowed.');} ?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div><!-- #content-page -->
            <div id="footer-wrapper" class="row">
                <div class="col">
                    <div id="footer">
                        <?php osc_run_hook('admin_content_footer'); ?>
                    </div>
                </div>
            </div>
        </div><!-- #content-render -->
    </div><!-- #content -->
    <?php osc_run_hook('admin_footer'); ?>
</body>
</html>