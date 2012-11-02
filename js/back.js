
window.addLoadEvent = function(func) {
   var oldonload = window.onload;
   if (typeof window.onload != 'function') {
      window.onload = func;
   } else {
      window.onload = function() {
         if (oldonload) {
            oldonload();
         }
         func();
      }
   }
}


var linki = {
  main : function() {
    
    linki = document.getElementsByTagName("a");

    for(i=0; i < linki.length; i++)

    {

    if (linki[i].className=='back')

    linki[i].onclick = back;

    }
    }
}
function back(){
 if (history.length>1)
 {
  if(document.referrer.indexOf(document.location.hostname)>-1)
    {
       history.back();
       return false;
    }
    else
    {
      return true
    }
  }
  else
  {
  return true;
  }
}
window.addLoadEvent(function() { linki.main(); });