/**
 * Online Consultant Module for Cotonti
 * @package Online Consultant
 * @author Alex
 * @copyright  © Portal30 Studio http://portal30.ru 2011-2013
 */


/**
 * Проиграть звук
 * @param wav_file
 */
function playSound(wav_file) {
    var canPlayWav = false;
    var agt = navigator.userAgent.toLowerCase();
    //if (agt.indexOf('mozilla') != 0){
        try {
            // параметр 'src' обязателен для Opera 10,
            myAudio = new Audio(wav_file);
            if (myAudio.canPlayType) {
                // CanPlayType returns maybe, probably, or an empty string.
                canPlayWav = ("no" != myAudio.canPlayType("audio/x-wav")) && ("" != myAudio.canPlayType("audio/x-wav"));
            }
        } catch (e) {
            canPlayWav = false;
        }
    //}

    if (canPlayWav){
        try {
            myAudio.play();
        } catch (e) {
            canPlayWav = false;
        }
    }
    if(!canPlayWav){
        var player = document.createElement("div");
        if(agt.indexOf('opera') != -1) {
            player.style = "position: absolute; left: 0px; top: -200px;";
        }
        document.body.appendChild(player);
        player.innerHTML = '<embed src="'+wav_file+'" hidden="true" autostart="true" loop="false">';
    }
}


/**
 * Экранирование символов
 * @param str
 * @returns str
 */
function htmlescape(str) {
    return str.replace('&','&amp;').replace('<','&lt;').replace('>','&gt;').replace('"','&quot;');
}