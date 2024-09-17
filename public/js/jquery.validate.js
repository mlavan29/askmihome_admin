
var CryptoJSAesJson = {
    stringify: function (cipherParams) {
        var j = {ct: cipherParams.ciphertext.toString(CryptoJS.enc.Base64)};
        if (cipherParams.iv) j.iv = cipherParams.iv.toString();
        if (cipherParams.salt) j.s = cipherParams.salt.toString();
        return JSON.stringify(j);
    },
    parse: function (jsonStr) {
        var j = JSON.parse(jsonStr);
        var cipherParams = CryptoJS.lib.CipherParams.create({ciphertext: CryptoJS.enc.Base64.parse(j.ct)});
        if (j.iv) cipherParams.iv = CryptoJS.enc.Hex.parse(j.iv)
        if (j.s) cipherParams.salt = CryptoJS.enc.Hex.parse(j.s)
        return cipherParams;
    }
}

var firebaseConfig = {
    apiKey: JSON.parse(CryptoJS.AES.decrypt($.cookie('apiKey'),atob($.cookie('haskey')),{format: CryptoJSAesJson}).toString(CryptoJS.enc.Utf8)),
    authDomain: JSON.parse(CryptoJS.AES.decrypt($.cookie('authDomain'),atob($.cookie('haskey')),{format: CryptoJSAesJson}).toString(CryptoJS.enc.Utf8)),
    databaseURL: JSON.parse(CryptoJS.AES.decrypt($.cookie('databaseURL'),atob($.cookie('haskey')),{format: CryptoJSAesJson}).toString(CryptoJS.enc.Utf8)),
    projectId: JSON.parse(CryptoJS.AES.decrypt($.cookie('projectId'),atob($.cookie('haskey')),{format: CryptoJSAesJson}).toString(CryptoJS.enc.Utf8)),
    storageBucket: JSON.parse(CryptoJS.AES.decrypt($.cookie('storageBucket'),atob($.cookie('haskey')),{format: CryptoJSAesJson}).toString(CryptoJS.enc.Utf8)),
    messagingSenderId: JSON.parse(CryptoJS.AES.decrypt($.cookie('messagingSenderId'),atob($.cookie('haskey')),{format: CryptoJSAesJson}).toString(CryptoJS.enc.Utf8)),
    appId: JSON.parse(CryptoJS.AES.decrypt($.cookie('appId'),atob($.cookie('haskey')),{format: CryptoJSAesJson}).toString(CryptoJS.enc.Utf8)),
    measurementId: JSON.parse(CryptoJS.AES.decrypt($.cookie('measurementId'),atob($.cookie('haskey')),{format: CryptoJSAesJson}).toString(CryptoJS.enc.Utf8))
}

firebase.initializeApp(firebaseConfig);
