import java.io.FileInputStream
import java.util.Properties

plugins {
    id("com.android.application")
    id("kotlin-android")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

val keystoreProperties = Properties()
val keystorePropertiesFile = rootProject.file("key.properties")
val hasReleaseKeystore = keystorePropertiesFile.exists()
if (hasReleaseKeystore) {
    keystoreProperties.load(FileInputStream(keystorePropertiesFile))
}

android {
    namespace = "com.pegasus.academy.pegasus_academy"
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    compileOptions {
        isCoreLibraryDesugaringEnabled = true
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = JavaVersion.VERSION_17.toString()
    }

    defaultConfig {
        // TODO: Specify your own unique Application ID (https://developer.android.com/studio/build/application-id.html).
        applicationId = "com.pegasus.academy"
        // You can update the following values to match your application needs.
        // For more information, see: https://flutter.dev/to/review-gradle-config.
        minSdk = flutter.minSdkVersion
        targetSdk = flutter.targetSdkVersion
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    signingConfigs {
        create("release") {
            if (hasReleaseKeystore) {
                keyAlias = keystoreProperties.getProperty("keyAlias")
                    ?: error("key.properties: missing keyAlias")
                keyPassword = keystoreProperties.getProperty("keyPassword")
                    ?: error("key.properties: missing keyPassword")
                storePassword = keystoreProperties.getProperty("storePassword")
                    ?: error("key.properties: missing storePassword")
                val storeFileProp = keystoreProperties.getProperty("storeFile")
                    ?: error("key.properties: missing storeFile")
                val keystoreFile = rootProject.file(storeFileProp)
                storeFile = keystoreFile
                check(keystoreFile.exists()) {
                    "Keystore not found: ${keystoreFile.absolutePath} (check storeFile in key.properties)"
                }
            }
        }
    }

    buildTypes {
        release {
            signingConfig = if (hasReleaseKeystore) {
                signingConfigs.getByName("release")
            } else {
                signingConfigs.getByName("debug")
            }
        }
    }

    // مكتبات JNI غير مضغوطة ومحاذاة مناسبة لأجهزة صفحة ذاكرة 16 كيلوبايت (متطلبات Play)
    packaging {
        jniLibs {
            useLegacyPackaging = false
        }
    }
}

// Google Play يرفض الحزم الموقّعة بمفتاح التطوير (debug)
afterEvaluate {
    listOf("bundleRelease", "assembleRelease").forEach { taskName ->
        tasks.findByName(taskName)?.doFirst {
            if (!keystorePropertiesFile.exists()) {
                throw org.gradle.api.GradleException(
                    """
                    |Missing android/key.properties — Play Store does not accept debug-signed APK/AAB.
                    |
                    |1) Create keystore (one time), from repo root:
                    |     cd mobile/android/app
                    |     keytool -genkey -v -keystore upload-keystore.jks -storetype JKS -keyalg RSA -keysize 2048 -validity 10000 -alias upload
                    |
                    |2) Copy mobile/android/key.properties.example → mobile/android/key.properties
                    |   Fill storePassword, keyPassword, keyAlias, storeFile (e.g. app/upload-keystore.jks).
                    |
                    |3) Build: flutter build appbundle --release
                    |
                    |https://docs.flutter.dev/deployment/android#sign-the-app
                    """.trimMargin(),
                )
            }
        }
    }
}

flutter {
    source = "../.."
}

dependencies {
    coreLibraryDesugaring("com.android.tools:desugar_jdk_libs:2.1.4")
}
