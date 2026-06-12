# WheelWash APK Build Guide

This monorepo has two separate Expo apps:

- Customer app: `D:\xampp\htdocs\app\WheelWash`
- Operations app: `D:\xampp\htdocs\app\opratinalapp`

Both apps are configured to use:

- API: `https://wheelwash.gutargu.app/api`
- Storage: `https://wheelwash.gutargu.app/storage`

## Login To EAS

Run this once:

```bash
npx eas-cli@latest login
```

Use your Expo account email/password in the browser or terminal prompt.

## Build Customer Preview APK

```bash
cd D:\xampp\htdocs\app\WheelWash
npx eas-cli@latest build:configure
npx eas-cli@latest build --platform android --profile preview
```

The `preview` profile builds an Android APK for direct sharing/testing.

## Build Operations Preview APK

```bash
cd D:\xampp\htdocs\app\opratinalapp
npx eas-cli@latest build:configure
npx eas-cli@latest build --platform android --profile preview
```

## Where The Download Link Appears

When EAS finishes, the terminal shows a build URL. Open that URL in your browser. The EAS build page has the APK download button/link.

## Share APK Link On WhatsApp

Copy the APK download link from the EAS build page and send it on WhatsApp. Testers can open the link on Android and download the APK.

## How Testers Install APK

1. Open the APK link on Android.
2. Download the APK.
3. If Android blocks install, enable install from unknown sources for the browser/files app.
4. Tap the APK again and install.
5. Open the app and test against the live backend.

## APK Preview vs Play Store AAB

- APK preview: for internal testing and WhatsApp sharing. It can be installed directly on Android.
- Play Store AAB: for Google Play release. It cannot be installed directly like an APK.

## Production Build Commands

Customer app Play Store build:

```bash
cd D:\xampp\htdocs\app\WheelWash
npx eas-cli@latest build --platform android --profile production
```

Operations app Play Store build:

```bash
cd D:\xampp\htdocs\app\opratinalapp
npx eas-cli@latest build --platform android --profile production
```

## Do Not Commit

Do not commit these files/folders:

- `.env`
- `google-services.json`
- `*.log`
- `*.zip`
- `*.exe`
- `node_modules`
- `vendor`
- generated `android` / `ios` folders
