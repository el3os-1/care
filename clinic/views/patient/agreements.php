<?php
$title = 'Patient Agreement';
require __DIR__ . '/../shared/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h1 class="h4 mb-0">Patient Agreement</h1>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3 mb-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                        <h2 class="h5">Terms and Conditions</h2>
                        <p>By signing this agreement, you agree to the following terms:</p>
                        <ul>
                            <li>You will attend scheduled sessions or provide 24-hour notice for cancellations.</li>
                            <li>You understand that therapy is confidential except in cases of imminent danger.</li>
                            <li>You agree to provide accurate information during intake and sessions.</li>
                            <li>You understand that late cancellations may incur fees.</li>
                            <li>You agree to follow the treatment plan recommended by your therapist.</li>
                        </ul>

                        <h2 class="h5">Privacy Policy</h2>
                        <p>Your personal information will be kept confidential and secure. We comply with HIPAA regulations.</p>

                        <h2 class="h5">Emergency Procedures</h2>
                        <p>In case of emergency, contact local crisis services or use the emergency button in your dashboard.</p>
                    </div>

                    <form method="POST" class="row g-3">
                        <div class="col-12 form-check">
                            <input class="form-check-input" type="checkbox" name="agree" id="agree" value="1" required>
                            <label class="form-check-label" for="agree">
                                I have read and agree to the terms and conditions.
                            </label>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="signature">Electronic Signature</label>
                            <input class="form-control" type="text" id="signature" name="signature" placeholder="Type your full name" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">Sign Agreement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../shared/footer.php'; ?>