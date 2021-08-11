                        <?php if (!defined('OC_ADMIN')) {
                            exit('Direct access is not allowed.');
                        } ?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div><!-- #content-page -->
            <footer id="footer-wrapper" class="row">
                <div class="col">
                    <div id="footer">
                        <?php osc_run_hook('admin_content_footer'); ?>
                    </div>
                </div>
            </footer>
        </main><!-- #content-render -->
    </div><!-- #content -->
    <?php osc_run_hook('admin_footer'); ?>
<script>
    window.onresize = resetSidebar;
    function resetSidebar() {
        var sC;
        var eH;
        var eS;
        var bE;
        if (window.matchMedia("(max-width: 768px)").matches) {
            eH = document.getElementById("header");
            eS = document.getElementById("sidebar-wrapper");
            eS.classList.add('offcanvas');
            eS.classList.add('offcanvas-start');
            eS.style.marginTop = eH.offsetHeight + "px";
            /* The viewport is less than, or equal to, 768 pixels wide */
        } else {
            eS = document.querySelector("#sidebar-wrapper.offcanvas");
            if (eS !== null) {
                sC = bootstrap.Offcanvas.getOrCreateInstance(eS);
                if (sC) {
                    eS.classList.remove('offcanvas');
                    eS.classList.remove('offcanvas-start');
                    eS.classList.remove('show');
                    eS.removeAttribute('style');
                    eS.removeAttribute('aria-hidden');
                    document.querySelector('body').removeAttribute('style');
                    bE = document.querySelector("#content > div > .modal-backdrop.fade.show");
                    if (bE !== null) {
                        bE.remove()
                    }
                }
            }
            /* The viewport is greater than 768 pixels wide */
        }
    }
    resetSidebar();
</script>
</body>
</html>