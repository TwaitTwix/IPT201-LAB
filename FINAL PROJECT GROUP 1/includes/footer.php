    </main>
        <footer class="footer">
            <div>
                <p>Dataset source: UCI Student Performance dataset by Paulo Cortez and A. Silva (CC BY 4.0).</p>
            </div>
        </footer>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('.sidebar-toggle');
        if (!toggle) return;
        toggle.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-open');
        });
    });
</script>
</body>
</html>
