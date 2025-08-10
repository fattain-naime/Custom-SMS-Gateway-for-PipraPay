<?php
if (!defined('pp_allowed_access')) {
    die('Direct access not allowed');
}

$plugin_slug = 'sms-notification';
$settings = pp_get_plugin_setting($plugin_slug);
?>

<form id="smsSettingsForm" method="post" action="">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">SMS Notification Settings</h1>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-grid gap-3 gap-lg-5">
                <!-- Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h4">Gateway Configuration</h2>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <input type="hidden" name="action" value="plugin_update-submit">
                        <input type="hidden" name="plugin_slug" value="<?php echo $plugin_slug ?>">

                        <!-- Gateway Selection -->
                        <div class="row mb-4">
                            <div class="col-sm-12">
                                <label for="sms_gateway" class="col-sm-12 col-form-label form-label">SMS Gateway</label>
                                <div class="input-group">
                                    <?php $gateway_value = isset($settings['sms_gateway']) ? strtolower($settings['sms_gateway']) : 'bulksmsbd'; ?>
                                    <select class="form-control" name="sms_gateway" id="sms_gateway" onchange="changeGatewayTab()">
                                        <option value="bulksmsbd" <?php echo ($gateway_value === 'bulksmsbd') ? 'selected' : ''; ?>>BulkSMSBD</option>
                                        <option value="mimsms"    <?php echo ($gateway_value === 'mimsms')    ? 'selected' : ''; ?>>MIMSMS</option>
                                        <option value="greenweb"  <?php echo ($gateway_value === 'greenweb')  ? 'selected' : ''; ?>>GreenWeb</option>
                                        <option value="custom"    <?php echo ($gateway_value === 'custom')    ? 'selected' : ''; ?>>Custom SMS Gateway</option>
                                    </select>
                                </div>
                                <small class="text-muted">Select your preferred SMS gateway provider</small>
                            </div>
                        </div>
                        <!-- Gateway Tabs -->
                        <ul class="nav nav-tabs flex-wrap mb-4 gateway-tabs-3col" id="gatewayTabs" role="tablist">
                            <li class="nav-item mb-2" role="presentation">
                                <button class="nav-link w-100 <?php echo ($gateway_value === 'bulksmsbd') ? 'active' : ''; ?>"
                                    id="bulksmsbd-tab" data-bs-toggle="tab" data-bs-target="#bulksmsbd"
                                    type="button" role="tab">BulkSMSBD</button>
                            </li>
                            <li class="nav-item mb-2" role="presentation">
                                <button class="nav-link w-100 <?php echo ($gateway_value === 'mimsms') ? 'active' : ''; ?>"
                                    id="mimsms-tab" data-bs-toggle="tab" data-bs-target="#mimsms"
                                    type="button" role="tab">MIMSMS</button>
                            </li>
                            <li class="nav-item mb-2" role="presentation">
                                <button class="nav-link w-100 <?php echo ($gateway_value === 'greenweb') ? 'active' : ''; ?>"
                                    id="greenweb-tab" data-bs-toggle="tab" data-bs-target="#greenweb"
                                    type="button" role="tab">GreenWeb</button>
                            </li>
                            <li class="nav-item mb-2" role="presentation">
                                <button class="nav-link w-100 <?php echo ($gateway_value === 'custom') ? 'active' : ''; ?>"
                                    id="custom-tab" data-bs-toggle="tab" data-bs-target="#custom"
                                    type="button" role="tab">Custom SMS Gateway</button>
                            </li>
                        </ul>

                        <style>
                            /* Force 3 columns at all breakpoints */
                            .gateway-tabs-3col {
                                display: flex;
                                flex-wrap: wrap;
                            }
                            .gateway-tabs-3col .nav-item {
                                flex: 0 0 33.3333%;
                                max-width: 33.3333%;
                            }
                            .gateway-tabs-3col .nav-link {
                                width: 100%;
                                text-align: center;
                            }

                            /* Optional: tidy tab borders when using nav-tabs */
                            .gateway-tabs-3col {
                                border-bottom: none;
                            }
                            .gateway-tabs-3col .nav-link {
                                border-bottom: 1px solid var(--bs-border-color);
                            }
                            .gateway-tabs-3col .nav-link.active {
                                border-bottom-color: #fff;
                            }
                        </style>

                        <!-- Tab Contents -->
                        <div class="tab-content" id="gatewayTabContent">
                            <!-- BulkSMSBD Configuration -->
                            <div class="tab-pane fade <?php echo ($gateway_value === 'bulksmsbd') ? 'show active' : ''; ?>" id="bulksmsbd" role="tabpanel" aria-labelledby="bulksmsbd-tab">
                                <div class="row mb-4">
                                    <div class="col-sm-6">
                                        <label for="bulksmsbd_api_key" class="col-sm-12 col-form-label form-label">API Key</label>
                                        <input type="password" class="form-control" name="bulksmsbd_api_key" id="bulksmsbd_api_key" value="<?= htmlspecialchars($settings['bulksmsbd_api_key'] ?? '') ?>">
                                        <small class="text-muted">Get your API key from BulkSMSBD dashboard</small>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="bulksmsbd_sender_id" class="col-sm-12 col-form-label form-label">Sender ID</label>
                                        <input type="text" class="form-control" name="bulksmsbd_sender_id" id="bulksmsbd_sender_id" value="<?= htmlspecialchars($settings['bulksmsbd_sender_id'] ?? '') ?>">
                                        <small class="text-muted">Max 11 characters (approved by BulkSMSBD)</small>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-sm-6">
                                        <label class="col-sm-12 col-form-label form-label">Message Type</label>
                                        <select class="form-control" name="bulksmsbd_type" id="bulksmsbd_type">
                                            <option value="text"    <?php echo (isset($settings['bulksmsbd_type']) && $settings['bulksmsbd_type'] === 'text') ? 'selected' : ''; ?>>Text (SMS)</option>
                                            <option value="unicode" <?php echo (isset($settings['bulksmsbd_type']) && $settings['bulksmsbd_type'] === 'unicode') ? 'selected' : ''; ?>>Unicode (Bangla)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- MIMSMS Configuration -->
                            <div class="tab-pane fade <?php echo ($gateway_value === 'mimsms') ? 'show active' : ''; ?>" id="mimsms" role="tabpanel" aria-labelledby="mimsms-tab">
                                <div class="row mb-4">
                                    <div class="col-sm-6">
                                        <label for="mimsms_api_key" class="col-sm-12 col-form-label form-label">API Key</label>
                                        <input type="password" class="form-control" name="mimsms_api_key" id="mimsms_api_key" value="<?= htmlspecialchars($settings['mimsms_api_key'] ?? '') ?>">
                                        <small class="text-muted">Get your API key from MIMSMS dashboard</small>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="mimsms_sender_id" class="col-sm-12 col-form-label form-label">Sender ID</label>
                                        <input type="text" class="form-control" name="mimsms_sender_id" id="mimsms_sender_id" value="<?= htmlspecialchars($settings['mimsms_sender_id'] ?? '') ?>">
                                        <small class="text-muted">Approved sender ID from MIMSMS</small>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-sm-6">
                                        <label for="mimsms_username" class="col-sm-12 col-form-label form-label">Username</label>
                                        <input type="text" class="form-control" name="mimsms_username" id="mimsms_username" value="<?= htmlspecialchars($settings['mimsms_username'] ?? '') ?>">
                                        <small class="text-muted">MIMSMS API endpoint</small>
                                    </div>
                                </div>
                            </div>

                            <!-- GreenWeb Configuration -->
                            <div class="tab-pane fade <?php echo ($gateway_value === 'greenweb') ? 'show active' : ''; ?>" id="greenweb" role="tabpanel" aria-labelledby="greenweb-tab">
                                <div class="row mb-4">
                                    <div class="col-sm-6">
                                        <label for="greenweb_api_token" class="col-sm-12 col-form-label form-label">API Token</label>
                                        <input type="password" class="form-control" name="greenweb_api_token" id="greenweb_api_token" value="<?= htmlspecialchars($settings['greenweb_api_token'] ?? '') ?>">
                                        <small class="text-muted">Get your token from GreenWeb dashboard</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom SMS Gateway Configuration -->
                            <div class="tab-pane fade <?php echo ($gateway_value === 'custom') ? 'show active' : ''; ?>" id="custom" role="tabpanel" aria-labelledby="custom-tab">
                                <div class="row mb-4">
                                    <div class="col-sm-12">
                                        <label for="custom_base_url" class="col-sm-12 col-form-label form-label">Base URL</label>
                                        <input type="text" class="form-control" name="custom_base_url" id="custom_base_url" placeholder="https://example.com/api/send.php" value="<?= htmlspecialchars($settings['custom_base_url'] ?? '') ?>">
                                        <small class="text-muted">Full endpoint without query string. We will append parameters like key, number, message, option, type, prioritize.</small>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-sm-6">
                                        <label for="custom_api_key" class="col-sm-12 col-form-label form-label">API Key</label>
                                        <input type="password" class="form-control" name="custom_api_key" id="custom_api_key" value="<?= htmlspecialchars($settings['custom_api_key'] ?? '') ?>">
                                        <small class="text-muted">Your Custom SMS API key</small>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="custom_device" class="col-sm-12 col-form-label form-label">Device (option)</label>
                                        <input type="text" class="form-control" name="custom_device" id="custom_device" placeholder="e.g., 1" value="<?= htmlspecialchars($settings['custom_device'] ?? '') ?>">
                                        <small class="text-muted">Maps to the <code>option</code> parameter (device id)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Common Settings -->
                        <div class="card-header mt-4">
                            <h2 class="card-title h4">Notification Triggers</h2>
                        </div>
                        <br>
                        <div class="row mb-4">
                            <div class="col-sm-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_invoice_created" id="enable_invoice_created" <?php echo (isset($settings['enable_invoice_created']) && $settings['enable_invoice_created']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_invoice_created">Send SMS when invoice is created</label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_transaction_complete" id="enable_transaction_complete" <?php echo (isset($settings['enable_transaction_complete']) && $settings['enable_transaction_complete']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_transaction_complete">Send SMS when transaction is completed</label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="enable_invoice_paid" id="enable_invoice_paid" <?php echo (isset($settings['enable_invoice_paid']) && $settings['enable_invoice_paid']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_invoice_paid">Send SMS when invoice is paid</label>
                                </div>
                            </div>
                        </div>

                        <div id="ajaxResponse" class="mb-3"></div>
                        <button type="submit" class="btn btn-primary btn-primary-add">Save Settings</button>
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
                <div id="stickyBlockEndPoint"></div>
            </div>
        </div>
    </div>
</form>

<script>
    function changeGatewayTab() {
        const gateway = document.getElementById('sms_gateway').value;
        const tabButton = document.getElementById(`${gateway}-tab`);
        if (tabButton) {
            const tab = new bootstrap.Tab(tabButton);
            tab.show();
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        changeGatewayTab();
    });
</script>

<script>
    $(document).ready(function() {
        $('#smsSettingsForm').on('submit', function(e) {
            e.preventDefault();
            document.querySelector(".btn-primary-add").innerHTML =
            '<div class="spinner-border text-light spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    document.querySelector(".btn-primary-add").innerHTML = 'Save Settings';
                    if (response.status) {
                        $('#ajaxResponse').removeClass('alert-danger').addClass('alert alert-success').html(response.message);
                    } else {
                        $('#ajaxResponse').removeClass('alert-success').addClass('alert alert-danger').html(response.message);
                    }
                },
                error: function() {
                    $('#ajaxResponse').removeClass('alert-success').addClass('alert alert-danger').html('An error occurred. Please try again.');
                }
            });
        });
    });
</script>
