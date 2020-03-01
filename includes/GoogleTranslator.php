<?php
// Detailed info on Google Translator API https://cloud.google.com/translate/
// This webERP-style code is based on http://hayageek.com/google-translate-api-tutorial/
function translate_via_google_translator($TextToTranslate, $TargetLanguage, $SourceLanguage) {
	$url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=' . $SourceLanguage . '&tl=' . $TargetLanguage . '&dt=t&q=' . urlencode($TextToTranslate);
	$Result = file_get_contents($url);
	//	shell_exec('wget "https://translate.googleapis.com/translate_a/single?client=gtx&sl=' . $SourceLanguage . '&tl=' . $TargetLanguage. '&dt=t&q=' . urlencode($TextToTranslate) . '" -O ' . sys_get_temp_dir() . '/translation.json -q');
	$TranslatedText = json_decode($Result);
	sleep(rand(1, 60));
	return $TranslatedText[0][0][0];
}

?>