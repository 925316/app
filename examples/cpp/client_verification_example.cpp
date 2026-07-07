#include <iostream>
#include <map>
#include <sstream>
#include <stdexcept>
#include <string>
#include <vector>

#include <openssl/bio.h>
#include <openssl/evp.h>
#include <openssl/pem.h>

namespace {

using Object = std::map<std::string, std::string>;

std::string escapeJsonString(const std::string& value)
{
    std::ostringstream output;

    for (const unsigned char character : value) {
        switch (character) {
        case '"':
            output << "\\\"";
            break;
        case '\\':
            output << "\\\\";
            break;
        case '\b':
            output << "\\b";
            break;
        case '\f':
            output << "\\f";
            break;
        case '\n':
            output << "\\n";
            break;
        case '\r':
            output << "\\r";
            break;
        case '\t':
            output << "\\t";
            break;
        default:
            if (character < 0x20) {
                output << "\\u";
                output << std::hex << std::uppercase;
                output.width(4);
                output.fill('0');
                output << static_cast<int>(character);
            } else {
                output << character;
            }
        }
    }

    return output.str();
}

std::string canonicalizeObject(const Object& object)
{
    std::ostringstream output;
    output << '{';

    bool first = true;
    for (const auto& entry : object) {
        if (!first) {
            output << ',';
        }

        first = false;
        output << '"' << escapeJsonString(entry.first) << "\":\"" << escapeJsonString(entry.second) << '"';
    }

    output << '}';

    return output.str();
}

std::vector<unsigned char> decodeBase64(const std::string& encoded)
{
    BIO* base64 = BIO_new(BIO_f_base64());
    BIO* memory = BIO_new_mem_buf(encoded.data(), static_cast<int>(encoded.size()));

    if (base64 == nullptr || memory == nullptr) {
        BIO_free_all(base64);
        BIO_free_all(memory);
        throw std::runtime_error("Failed to allocate OpenSSL BIO objects.");
    }

    BIO_set_flags(base64, BIO_FLAGS_BASE64_NO_NL);
    memory = BIO_push(base64, memory);

    std::vector<unsigned char> decoded(encoded.size());
    const int decodedLength = BIO_read(memory, decoded.data(), static_cast<int>(decoded.size()));
    BIO_free_all(memory);

    if (decodedLength <= 0) {
        throw std::runtime_error("Failed to decode base64 signature.");
    }

    decoded.resize(static_cast<std::size_t>(decodedLength));

    return decoded;
}

EVP_PKEY* loadPublicKey(const std::string& publicKeyPem)
{
    BIO* memory = BIO_new_mem_buf(publicKeyPem.data(), static_cast<int>(publicKeyPem.size()));

    if (memory == nullptr) {
        throw std::runtime_error("Failed to allocate public key BIO.");
    }

    EVP_PKEY* publicKey = PEM_read_bio_PUBKEY(memory, nullptr, nullptr, nullptr);
    BIO_free(memory);

    if (publicKey == nullptr) {
        throw std::runtime_error("Failed to parse PEM public key.");
    }

    return publicKey;
}

bool verifyRsaSha256(
    const std::string& canonicalPayload,
    const std::string& base64Signature,
    const std::string& publicKeyPem)
{
    std::vector<unsigned char> signature = decodeBase64(base64Signature);
    EVP_PKEY* publicKey = loadPublicKey(publicKeyPem);
    EVP_MD_CTX* context = EVP_MD_CTX_new();

    if (context == nullptr) {
        EVP_PKEY_free(publicKey);
        throw std::runtime_error("Failed to allocate OpenSSL digest context.");
    }

    const int initOk = EVP_DigestVerifyInit(context, nullptr, EVP_sha256(), nullptr, publicKey);
    const int updateOk = EVP_DigestVerifyUpdate(context, canonicalPayload.data(), canonicalPayload.size());
    const int verifyOk = EVP_DigestVerifyFinal(context, signature.data(), signature.size());

    EVP_MD_CTX_free(context);
    EVP_PKEY_free(publicKey);

    if (initOk != 1 || updateOk != 1) {
        throw std::runtime_error("OpenSSL verification setup failed.");
    }

    return verifyOk == 1;
}

} // namespace

int main()
{
    const Object responseData = {
        {"expires_at", "2026-12-31 23:59:59"},
        {"license_key", "ABCDE-ABCDE-ABCDE-ABCDE"},
        {"status", "active"},
    };

    const std::string canonicalPayload = canonicalizeObject(responseData);

    // Replace these values with the `signature` field and public key from your environment.
    const std::string signatureFromApi = "base64-signature-from-api";
    const std::string publicKeyPem = R"PEM(-----BEGIN PUBLIC KEY-----
replace-with-your-public-key
-----END PUBLIC KEY-----
)PEM";

    std::cout << "Canonical payload to verify:\n" << canonicalPayload << "\n";
    std::cout << "Call POST /api/license/check, extract data + signature, then verify data only.\n";

    try {
        const bool verified = verifyRsaSha256(canonicalPayload, signatureFromApi, publicKeyPem);
        std::cout << (verified ? "Signature verification passed." : "Signature verification failed.") << "\n";
    } catch (const std::exception& exception) {
        std::cerr << "Verification example needs real API signature/public key values: " << exception.what() << "\n";
    }

    return 0;
}
