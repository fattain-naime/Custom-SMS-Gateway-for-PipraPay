# SMS Notification for PipraPay

**Original Developer:** [PipraPay](https://piprapay.com)
**Contributed by:** [Fattain Naime](https://iamnaime.me)  
**Tags:** sms, notification, alert, invoice, transaction, bulksms, mimsms, greenweb, custom sms gateway  
**Requires at least:** 1.0.0  
**Tested up to:** 1.0.1  
**Stable tag:** 1.0.1  
**License:** GPL-2.0+  
**License URI:** [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)  

---

## 📌 Description

**SMS Notification for PipraPay** is a powerful plugin that sends automatic SMS alerts to customers for important payment-related events.  
It works with multiple SMS gateways, including **BulkSMSBD**, **MIMSMS**, **GreenWeb**, and a **Custom SMS Gateway** option.

### ✨ Key Features
- Automatic SMS notifications for:
  - Invoice creation  
  - Transaction completion  
  - Paid invoices  
- Multiple gateway support:
  - BulkSMSBD  
  - MIMSMS  
  - GreenWeb  
  - Custom SMS Gateway (set your own API URL, key, and device ID)  
- Easy admin panel configuration.  
- Mobile number validation (Bangladesh format).  

### 💡 Why Use SMS Notification?
Keeping customers informed builds trust. This plugin ensures customers instantly get notified via SMS about payment updates — no manual work required.

---

## 🚀 Installation

1. Download the latest release and upload into PipraPay **Plugin** section.
2. Enable the **SMS Notification** module from your PipraPay admin panel.
3. Go to **Module → SMS Notification** and choose your preferred gateway.
4. Configure API credentials for your chosen gateway.
5. Enable the triggers you want:
   - Invoice Created
   - Transaction Complete
   - Invoice Paid
6. Save settings.

---

## ⚙️ Custom SMS Gateway Setup

If you choose **Custom SMS Gateway**, you can define:
- **Base URL** – API endpoint (e.g., `https://example.com/api/send.php`)
- **API Key** – Your unique API key from the SMS provider.
- **Device (option)** – The device/option parameter your provider requires.

The plugin will send GET requests like:

`https://your-sms-provider.com/send.php?key=API_KEY&number=8801XXXXXXXXX&message=Hello&option=1&type=sms&prioritize=0`

---

## 📝 Changelog

### 1.0.1
- Added **Custom SMS Gateway** option.
- New admin UI tab for Custom Gateway configuration.

### 1.0.0
- Initial release with BulkSMSBD, MIMSMS, and GreenWeb integration.

---

## 📜 License
This project is licensed under the **GPL-2.0+** License – see the [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) file for details.
