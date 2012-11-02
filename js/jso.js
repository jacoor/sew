var jso={
  gI:function(a,b){
    // get Element by ID
    if(!b)b=document;
    return b.getElementById(a)
  },
  gT:function(a,b,i){
    // get Elements by Tag Name
    if(!b)b=document;
    b=b.getElementsByTagName(a)
    a=[]
    for(i=0;i<b.length;i++)a[a.length]=b[i];
    return a
  },
  cE:function(a,b,c,d,e,i){
    // create Element
    a=document.createElement(a)
    for(i in b)a[i]=b[i];
    for(i in c)if(typeof c[i]!="function")a.appendChild(c[i].nodeType?c[i]:document.createTextNode(c[i]));;
    for(i in e)jso.aE(a,i,e[i]);
    if(d)d.appendChild(a);
    return a
  },
  gE:function(x){
    // get Element from Event
    x=x||window.event;
    x=x.target||x.srcElement;
    if(x.nodeType==3)x=x.parentNode;
    return x
  },
  aE:function(O,E,F){
    // add Event to Element
    return(O.x=O.addEventListener)?O.x(E,F,0):(O.x=O.attachEvent)?O.x('on'+E,F):!1;
  },
  rE:function(O,E,F){
    // remove  Event from Element
    return(O.x=O.removeEventListener)?O.x(E,F,0):(O.x=O.detachEvent)?O.x('on'+E,F):!1;
  }
} 
