$(document).ready(function(){
	$(".dropzone").dropzone({
		url: 'index.php?plugin=common_media&_spAction=addMedia&showHTML=0',
		width: 300,
		height: 300, 
		progressBarWidth: '100%',
		maxFileSize: '2MB',
		acceptedFiles: "image/*,application/pdf",
		autoProcessQueue: false
	})
});