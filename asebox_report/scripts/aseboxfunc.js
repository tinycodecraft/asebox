vbGeneralDate=0;vbLongDate=1;vbShortDate=2;vbLongTime=3;vbShortTime=4;vbUseSystemDayOfWeek=0;vbSunday=1;vbMonday=2;vbTuesday=3;vbWednesday=4;vbThursday=5;vbFriday=6;vbSaturday=7;vbUseSystem=0;vbFirstJan1=1;vbFirstFourDays=2;vbFirstFullWeek=3;Date.MonthNames=[null,"January","February","March","April","May","June","July","August","September","October","November","December"];Date.WeekdayNames=[null,"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];Date.IsDate=function(b){return !isNaN(new Date(b))};Date.CDate=function(f){if(Date.IsDate(f)){return new Date(f)}var c=f.replace(/\-/g,"/").replace(/\./g,"/").replace(/ /g,"/");c=c.replace(/pm$/i," pm").replace(/am$/i," am");if(Date.IsDate(c)){return new Date(c)}var b=c+"/"+new Date().getFullYear();if(Date.IsDate(b)){return new Date(b)}if(c.indexOf(":")){var e=c.replace(/ /,"/"+new Date().getFullYear()+" ");if(Date.IsDate(e)){return new Date(e)}var d=new Date().toDateString()+" "+f;if(Date.IsDate(d)){return new Date(d)}}return false};Date.DateAdd=function(d,b,e){if(!Date.CDate(e)){return"invalid date: '"+e+"'"}if(isNaN(b)){return"invalid number: '"+b+"'"}b=new Number(b);var c=Date.CDate(e);switch(d.toLowerCase()){case"yyyy":c.setFullYear(c.getFullYear()+b);break;case"q":c.setMonth(c.getMonth()+(b*3));break;case"m":c.setMonth(c.getMonth()+b);break;case"y":case"d":case"w":c.setDate(c.getDate()+b);break;case"ww":c.setDate(c.getDate()+(b*7));break;case"h":c.setHours(c.getHours()+b);break;case"n":c.setMinutes(c.getMinutes()+b);break;case"s":c.setSeconds(c.getSeconds()+b);break;case"ms":c.setMilliseconds(c.getMilliseconds()+b);break;default:return"invalid interval: '"+d+"'"}return c};Date.DateDiff=function(u,f,d,t){if(!Date.CDate(f)){return"invalid date: '"+f+"'"}if(!Date.CDate(d)){return"invalid date: '"+d+"'"}t=(isNaN(t)||t==0)?vbSunday:parseInt(t);var s=Date.CDate(f);var r=Date.CDate(d);if("h,n,s,ms".indexOf(u.toLowerCase())==-1){if(f.toString().indexOf(":")==-1){s.setUTCHours(0,0,0,0)}if(d.toString().indexOf(":")==-1){r.setUTCHours(0,0,0,0)}}var p=r.valueOf()-s.valueOf();var n=new Date(p);var j=r.getUTCFullYear()-s.getUTCFullYear();var o=r.getUTCMonth()-s.getUTCMonth()+(j!=0?j*12:0);var l=parseInt(o/3);var c=p;var k=parseInt(p/1000);var q=parseInt(k/60);var m=parseInt(q/60);var h=parseInt(m/24);var b=parseInt(h/7);if(u.toLowerCase()=="ww"){var e=Date.DatePart("w",s,t)-1;if(e){s.setDate(s.getDate()+7-e)}var e=Date.DatePart("w",r,t)-1;if(e){r.setDate(r.getDate()-e)}var g=Date.DateDiff("w",s,r)+1}switch(u.toLowerCase()){case"yyyy":return j;case"q":return l;case"m":return o;case"y":case"d":return h;case"w":return b;case"ww":return g;case"h":return m;case"n":return q;case"s":return k;case"ms":return c;default:return"invalid interval: '"+u+"'"}};Date.DatePart=function(d,e,c){if(!Date.CDate(e)){return"invalid date: '"+e+"'"}var b=Date.CDate(e);switch(d.toLowerCase()){case"yyyy":return b.getFullYear();case"q":return parseInt(b.getMonth()/3)+1;case"m":return b.getMonth()+1;case"y":return Date.DateDiff("y","1/1/"+b.getFullYear(),b)+1;case"d":return b.getDate();case"w":return Date.Weekday(b.getDay()+1,c);case"ww":return Date.DateDiff("ww","1/1/"+b.getFullYear(),b,c)+1;case"h":return b.getHours();case"n":return b.getMinutes();case"s":return b.getSeconds();case"ms":return b.getMilliseconds();default:return"invalid interval: '"+d+"'"}};Date.MonthName=function(c,b){if(isNaN(c)){if(!Date.CDate(c)){return"invalid month: '"+c+"'"}c=DatePart("m",Date.CDate(c))}var d=Date.MonthNames[c];if(b==true){d=d.substring(0,3)}return d};Date.WeekdayName=function(b,e,c){if(isNaN(b)){if(!Date.CDate(b)){return"invalid weekday: '"+b+"'"}b=DatePart("w",Date.CDate(b))}c=(isNaN(c)||c==0)?vbSunday:parseInt(c);var d=((c-1+parseInt(b)-1+7)%7)+1;var f=Date.WeekdayNames[d];if(e==true){f=f.substring(0,3)}return f};Date.Weekday=function(b,c){c=(isNaN(c)||c==0)?vbSunday:parseInt(c);return((parseInt(b)-c+7)%7)+1};Date.FormatDateTime=function(d,c){if(d.toUpperCase().substring(0,3)=="NOW"){d=new Date()}if(!Date.CDate(d)){return"invalid date: '"+d+"'"}if(isNaN(c)){c=vbGeneralDate}var b=Date.CDate(d);switch(parseInt(c)){case vbGeneralDate:return b.toString();case vbLongDate:return Format(d,"DDDD, MMMM D, YYYY");case vbShortDate:return Format(d,"MM/DD/YYYY");case vbLongTime:return b.toLocaleTimeString();case vbShortTime:return Format(d,"HH:MM:SS");default:return"invalid NamedFormat: '"+c+"'"}};Date.Format=function(j,h,g,f){if(!Date.CDate(j)){return"invalid date: '"+j+"'"}if(!h||h==""){return c.toString()}var c=Date.CDate(j);this.pad=function(n){if(n.toString().length==1){n="0"+n}return n};var k=c.getHours()>=12?"PM":"AM";var l=c.getHours();if(l==0){l=12}if(l>12){l-=12}var e=l+":"+this.pad(c.getMinutes())+":"+this.pad(c.getSeconds())+" "+k;var d=(c.getMonth()+1)+"/"+c.getDate()+"/"+new String(c.getFullYear()).substring(2,4);var m=Date.MonthName(c.getMonth()+1)+" "+c.getDate()+", "+c.getFullYear();var b=h;b=b.replace(new RegExp("C","gi"),"CCCC");b=b.replace(new RegExp("mmmm","gi"),"XXXX");b=b.replace(new RegExp("mmm","gi"),"XXX");b=b.replace(new RegExp("dddddd","gi"),"AAAAAA");b=b.replace(new RegExp("ddddd","gi"),"AAAAA");b=b.replace(new RegExp("dddd","gi"),"AAAA");b=b.replace(new RegExp("ddd","gi"),"AAA");b=b.replace(new RegExp("timezone","gi"),"ZZZZ");b=b.replace(new RegExp("time24","gi"),"TTTT");b=b.replace(new RegExp("time","gi"),"TTT");b=b.replace(new RegExp("yyyy","gi"),c.getFullYear());b=b.replace(new RegExp("yy","gi"),new String(c.getFullYear()).substring(2,4));b=b.replace(new RegExp("y","gi"),Date.DatePart("y",c));b=b.replace(new RegExp("q","gi"),Date.DatePart("q",c));b=b.replace(new RegExp("mm","gi"),this.pad(c.getMonth()+1));b=b.replace(new RegExp("m","gi"),(c.getMonth()+1));b=b.replace(new RegExp("dd","gi"),this.pad(c.getDate()));b=b.replace(new RegExp("d","gi"),c.getDate());b=b.replace(new RegExp("hh","gi"),this.pad(c.getHours()));b=b.replace(new RegExp("h","gi"),c.getHours());b=b.replace(new RegExp("nn","gi"),this.pad(c.getMinutes()));b=b.replace(new RegExp("n","gi"),c.getMinutes());b=b.replace(new RegExp("ss","gi"),this.pad(c.getSeconds()));b=b.replace(new RegExp("s","gi"),c.getSeconds());b=b.replace(new RegExp("t t t t t","gi"),e);b=b.replace(new RegExp("am/pm","g"),c.getHours()>=12?"pm":"am");b=b.replace(new RegExp("AM/PM","g"),c.getHours()>=12?"PM":"AM");b=b.replace(new RegExp("a/p","g"),c.getHours()>=12?"p":"a");b=b.replace(new RegExp("A/P","g"),c.getHours()>=12?"P":"A");b=b.replace(new RegExp("AMPM","g"),c.getHours()>=12?"pm":"am");b=b.replace(new RegExp("XXXX","gi"),Date.MonthName(c.getMonth()+1,false));b=b.replace(new RegExp("XXX","gi"),Date.MonthName(c.getMonth()+1,true));b=b.replace(new RegExp("AAAAAA","gi"),m);b=b.replace(new RegExp("AAAAA","gi"),d);b=b.replace(new RegExp("AAAA","gi"),Date.WeekdayName(c.getDay()+1,false,g));b=b.replace(new RegExp("AAA","gi"),Date.WeekdayName(c.getDay()+1,true,g));b=b.replace(new RegExp("TTTT","gi"),c.getHours()+":"+this.pad(c.getMinutes()));b=b.replace(new RegExp("TTT","gi"),l+":"+this.pad(c.getMinutes())+" "+k);b=b.replace(new RegExp("CCCC","gi"),d+" "+e);tz=c.getTimezoneOffset();timezone=(tz<0)?("GMT-"+tz/60):(tz==0)?("GMT"):("GMT+"+tz/60);b=b.replace(new RegExp("ZZZZ","gi"),timezone);return b};Date.getPreviousSunday=function(b){retVal=Date.DateAdd("d",-b.getDay()-7,b);return retVal};IsDate=Date.IsDate;CDate=Date.CDate;DateAdd=Date.DateAdd;DateDiff=Date.DateDiff;DatePart=Date.DatePart;MonthName=Date.MonthName;WeekdayName=Date.WeekdayName;Weekday=Date.Weekday;FormatDateTime=Date.FormatDateTime;Format=Date.Format;getPreviousSunday=Date.getPreviousSunday;if(!this.JSON){JSON={}}(function(){function f(n){return n<10?"0"+n:n}if(typeof Date.prototype.toJSON!=="function"){Date.prototype.toJSON=function(key){return this.getUTCFullYear()+"-"+f(this.getUTCMonth()+1)+"-"+f(this.getUTCDate())+"T"+f(this.getUTCHours())+":"+f(this.getUTCMinutes())+":"+f(this.getUTCSeconds())+"Z"};String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(key){return this.valueOf()}}var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={"\b":"\\b","\t":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"},rep;function quote(string){escapable.lastIndex=0;return escapable.test(string)?'"'+string.replace(escapable,function(a){var c=meta[a];return typeof c==="string"?c:"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+string+'"'}function str(key,holder){var i,k,v,length,mind=gap,partial,value=holder[key];if(value&&typeof value==="object"&&typeof value.toJSON==="function"){value=value.toJSON(key)}if(typeof rep==="function"){value=rep.call(holder,key,value)}switch(typeof value){case"string":return quote(value);case"number":return isFinite(value)?String(value):"null";case"boolean":case"null":return String(value);case"object":if(!value){return"null"}gap+=indent;partial=[];if(Object.prototype.toString.apply(value)==="[object Array]"){length=value.length;for(i=0;i<length;i+=1){partial[i]=str(i,value)||"null"}v=partial.length===0?"[]":gap?"[\n"+gap+partial.join(",\n"+gap)+"\n"+mind+"]":"["+partial.join(",")+"]";gap=mind;return v}if(rep&&typeof rep==="object"){length=rep.length;for(i=0;i<length;i+=1){k=rep[i];if(typeof k==="string"){v=str(k,value);if(v){partial.push(quote(k)+(gap?": ":":")+v)}}}}else{for(k in value){if(Object.hasOwnProperty.call(value,k)){v=str(k,value);if(v){partial.push(quote(k)+(gap?": ":":")+v)}}}}v=partial.length===0?"{}":gap?"{\n"+gap+partial.join(",\n"+gap)+"\n"+mind+"}":"{"+partial.join(",")+"}";gap=mind;return v}}if(typeof JSON.stringify!=="function"){JSON.stringify=function(value,replacer,space){var i;gap="";indent="";if(typeof space==="number"){for(i=0;i<space;i+=1){indent+=" "}}else{if(typeof space==="string"){indent=space}}rep=replacer;if(replacer&&typeof replacer!=="function"&&(typeof replacer!=="object"||typeof replacer.length!=="number")){throw new Error("JSON.stringify")}return str("",{"":value})}}if(typeof JSON.parse!=="function"){JSON.parse=function(text,reviver){var j;function walk(holder,key){var k,v,value=holder[key];if(value&&typeof value==="object"){for(k in value){if(Object.hasOwnProperty.call(value,k)){v=walk(value,k);if(v!==undefined){value[k]=v}else{delete value[k]}}}}return reviver.call(holder,key,value)}cx.lastIndex=0;if(cx.test(text)){text=text.replace(cx,function(a){return"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)})}if(/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,""))){j=eval("("+text+")");return typeof reviver==="function"?walk({"":j},""):j}throw new SyntaxError("JSON.parse")}}}());var timer=null;var OldDiv="";var TimerRunning=false;var largeur="210";var separateur="/";var calendrierSortie="";var today="";var current_month="";var current_year="";var current_day="";var current_day_since_start_week="";var month_name_FRE=new Array("Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre");var day_name_FRE=new Array("L","M","M","J","V","S","D");var month_name_ENG=new Array("January","February","March","April","May","June","July","August","September","October","November","December");var day_name_ENG=new Array("M","T","W","T","F","S","S");var month_name=null;var day_name=null;var myObjectClick=null;var classMove="moncalendrier";var lastInput=null;var div_calendar="";var year,month,day="";var timeDuChamps=0;var dformat=null;function $get(b){return document.getElementById(b)}function slideUp(b,c){if(parseInt($get(b).style.left)<0){$get(b).style.left=parseInt($get(b).style.left)+10+"px";$get(c).style.left=parseInt($get(c).style.left)+10+"px";timer=setTimeout('slideUp("'+b+'","'+c+'")',10)}else{clearTimeout(timer);TimerRunning=false;$get(c).parentNode.removeChild($get(c))}}function slideDown(b,c){if(parseInt($get(b).style.left)>0){$get(b).style.left=parseInt($get(b).style.left)-10+"px";$get(c).style.left=parseInt($get(c).style.left)-10+"px";timer=setTimeout('slideDown("'+b+'","'+c+'")',10)}else{clearTimeout(timer);TimerRunning=false;$get(c).parentNode.removeChild($get(c))}}function CreateDivTempo(d){if(!TimerRunning){var b=new Date();IdTemp=b.getMilliseconds();var c=document.createElement("DIV");c.style.position="absolute";c.style.top="0px";c.style.width="100%";c.className="ListeDate";c.id=IdTemp;c.innerHTML=CreateDayCalandar(year,month,day);$get("Contenant_Calendar").appendChild(c);if(d=="left"){TimerRunning=true;c.style.left="-"+largeur+"px";slideUp(c.id,OldDiv)}else{if(d=="right"){TimerRunning=true;c.style.left=largeur+"px";slideDown(c.id,OldDiv)}else{"";c.style.left=0+"px"}}$get("Contenant_Calendar").style.height=c.offsetHeight+"px";$get("Contenant_Calendar").style.zIndex="200";OldDiv=c.id}}function drop(){myObjectClick=null}function getClassDrag(myObject){with(myObject){var x=className;listeClass=x.split(" ");for(var i=0;i<listeClass.length;i++){if(listeClass[i]==classMove){myObjectClick=myObject;break}}}}function masquerSelect(){var b=navigator.userAgent.toLowerCase();var d=parseFloat(b.substring(b.indexOf("msie ")+5));var c=((b.indexOf("msie")!=-1)&&(b.indexOf("opera")==-1)&&(b.indexOf("webtv")==-1));if(c&&(d<7)){svn=document.getElementsByTagName("SELECT");for(a=0;a<svn.length;a++){svn[a].style.visibility="hidden"}}}function montrerSelect(){var b=navigator.userAgent.toLowerCase();var d=parseFloat(b.substring(b.indexOf("msie ")+5));var c=((b.indexOf("msie")!=-1)&&(b.indexOf("opera")==-1)&&(b.indexOf("webtv")==-1));if(c&&d<7){svn=document.getElementsByTagName("SELECT");for(a=0;a<svn.length;a++){svn[a].style.visibility="visible"}}}function createFrame(){var b=document.createElement("iframe");b.style.width=largeur+"px";b.style.height=div_calendar.offsetHeight+"px";b.style.zIndex="0";b.frameBorder="0";b.style.position="absolute";b.style.visibility="hidden";b.style.top=0+"px";b.style.left=0+"px";div_calendar.appendChild(b)}function annee_precedente(){if(current_year==1){current_year=current_year}else{current_year=current_year-1}CreateDivTempo("left")}function annee_suivante(){current_year=current_year+1;CreateDivTempo("right")}function mois_precedent(){if(current_month==0){current_month=11;current_year=current_year-1}else{current_month=current_month-1}CreateDivTempo("left")}function mois_suivant(){if(current_month==11){current_month=0;current_year=current_year+1}else{current_month=current_month+1}CreateDivTempo("right")}function calendrier(f,e,g){if(e==null||f==null){today=new Date()}else{today=new Date(f,e,g)}current_month=today.getMonth();current_year=today.getFullYear();current_day=today.getDate();var c='<a href="javascript:mois_precedent()" style="position:absolute;left:30px;z-index:200;" > < </a>';var b='<a href="javascript:mois_suivant()" style="position:absolute;right:30px;z-index:200;"> > </a>';var k='<a href="javascript:annee_suivante()" style="position:absolute;right:5px;z-index:200;" >&nbsp;&nbsp; > > </a>';var h='<a href="javascript:annee_precedente()" style="position:absolute;left:5px;z-index:200;"  > < < &nbsp;&nbsp;</a>';calendrierSortie='<p class="titleMonth" style="position:relative;z-index:200;"> <a href="javascript:alimenterChamps(\'\')" style="float:left;margin-left:3px;color:#cccccc;font-size:10px;z-index:200;"> Clear </a><a href="javascript:masquerCalendrier()" style="float:right;margin-right:3px;color:red;font-weight:bold;font-size:12px;z-index:200;">X</a>&nbsp;</p>';if(dformat=="dmy"){month_name=month_name_FRE;day_name=day_name_FRE}else{month_name=month_name_ENG;day_name=day_name_ENG}calendrierSortie+='<p class="titleMonth" style="float:left;position:relative;z-index:200;">'+k+h+c+'<span id="curentDateString">'+month_name[current_month]+" "+current_year+"</span>"+b+'</p><div id="Contenant_Calendar">';if(!document.getElementById("calendrier")){div_calendar=document.createElement("div");div_calendar.setAttribute("id","calendrier");div_calendar.className="calendar";var d=document.getElementsByTagName("body")[0];d.appendChild(div_calendar)}else{div_calendar=document.getElementById("calendrier")}var j=largeur+"px";calendrierSortie=calendrierSortie+'</div><div class="separator"></div>';div_calendar.innerHTML=calendrierSortie;div_calendar.style.width=j;CreateDivTempo("")}function CreateDayCalandar(){var d=new Date(current_year,current_month,1);current_day_since_start_week=((d.getDay()==0)?6:d.getDay()-1);var c=false;var f=(current_year%4)==0?29:28;var e=new Array(31,f,31,30,31,30,31,31,30,31,30,31);var b=0;var g="";var j="";for(var h=0;h<(e[current_month]+current_day_since_start_week);h++){if(c==false){for(b=0;b<7;b++){if(b==6){g+="<span>"+day_name[b]+"</span>"}else{g+="<span>"+day_name[b]+"</span>"}}c=true}if(h<e[current_month]){if(current_day==(h+1)){j+='<span onclick="alimenterChamps(this.innerHTML)" class="currentDay DayDate">'+(h+1)+"</span>"}else{j+='<span class="DayDate" onclick="alimenterChamps(this.innerHTML)">'+(h+1)+"</span>"}}}for(i=0;i<current_day_since_start_week;i++){j="<span>&nbsp;</span>"+j}$get("curentDateString").innerHTML=month_name[current_month]+" "+current_year;return(g+j)}function initialiserCalendrier(b){myObjectClick=b;if(myObjectClick.disabled!=true){if(myObjectClick.value!=""){dformat=getDateFormat();if(dformat=="mdy"){month_name=month_name_FRE;day_name=day_name_FRE}else{month_name=month_name_ENG;day_name=day_name_ENG}var f=trim(myObjectClick.value);var g=f.split(" ");var c=g[0];if(g[1]!=null){timeDuChamps=g[1]}var e=new RegExp("/","g");var d=c.split(e);if(dformat=="mdy"){calendrier(d[2],d[0]-1,d[1])}else{calendrier(d[2],d[1]-1,d[0])}}else{calendrier(b)}positionCalendar(b);masquerSelect();createFrame()}}function ds_getleft(c){var b=c.offsetLeft;c=c.offsetParent;while(c){b+=c.offsetLeft;c=c.offsetParent}return b}function ds_gettop(c){var b=c.offsetTop;c=c.offsetParent;while(c){b+=c.offsetTop;c=c.offsetParent}return b}function positionCalendar(b){document.getElementById("calendrier").style.left=ds_getleft(b)+"px";document.getElementById("calendrier").style.top=ds_gettop(b)+20+"px";document.getElementById("calendrier").style.visibility="visible"}function alimenterChamps(c){if(c!=""){var b=getDateFormat();if(timeDuChamps!=0){tmStr=" "+timeDuChamps}else{tmStr=""}if(b=="mdy"){lastInput.value=formatInfZero((current_month+1))+separateur+formatInfZero(c)+separateur+current_year+tmStr}else{lastInput.value=formatInfZero(c)+separateur+formatInfZero((current_month+1))+separateur+current_year+tmStr}}else{lastInput.value=""}masquerCalendrier()}function masquerCalendrier(){document.getElementById("calendrier").style.visibility="hidden";montrerSelect()}function formatInfZero(b){if(parseInt(b)<10){b="0"+b}return b}function CreateSpan(){var b=document.createElement("span");b.className="";b.innerText="";b.onClick="";return b}var max=100;var min=0;var opacite=min;up=true;var IsIE=!!document.all;function fadePic(){try{var c=document.getElementById("calendrier");if(opacite<max&&up){opacite+=5}if(opacite>min&&!up){opacite-=5}IsIE?c.filters[0].opacity=opacite:document.getElementById("calendrier").style.opacity=opacite/100;if(opacite<max&&up){timer=setTimeout("fadePic()",10)}else{if(opacite>min&&!up){timer=setTimeout("fadePic()",10)}else{if(opacite==max){up=false}if(opacite<=min){up=true}clearTimeout(timer)}}}catch(b){alert(b.message)}}function getDateFormat(){var b=document.getElementsByName("DFormat")[0].value;return b}function dispcalend(c){var b=document.getElementsByName(c)[0];initialiserCalendrier(b);lastInput=b}var MONTH_NAMES=new Array("January","February","March","April","May","June","July","August","September","October","November","December","Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");var DAY_NAMES=new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sun","Mon","Tue","Wed","Thu","Fri","Sat");function LZ(b){return(b<0||b>9?"":"0")+b}function isDate(d,c){var b=getDateFromFormat(d,c);if(b==0){return false}return true}function compareDates(f,g,d,e){var c=getDateFromFormat(f,g);var b=getDateFromFormat(d,e);if(c==0||b==0){return -1}else{if(c>b){return 1}}return 0}function formatDate(L,G){G=G+"";var o="";var x=0;var J="";var g="";var n=L.getYear()+"";var j=L.getMonth()+1;var I=L.getDate();var q=L.getDay();var p=L.getHours();var A=L.getMinutes();var t=L.getSeconds();var v,w,e,u,N,f,F,D,B,r,P,p,O,l,b,C;var z=new Object();if(n.length<4){n=""+(n-0+1900)}z.y=""+n;z.yyyy=n;z.yy=n.substring(2,4);z.M=j;z.MM=LZ(j);z.MMM=MONTH_NAMES[j-1];z.NNN=MONTH_NAMES[j+11];z.d=I;z.dd=LZ(I);z.E=DAY_NAMES[q+7];z.EE=DAY_NAMES[q];z.H=p;z.HH=LZ(p);if(p==0){z.h=12}else{if(p>12){z.h=p-12}else{z.h=p}}z.hh=LZ(z.h);if(p>11){z.K=p-12}else{z.K=p}z.k=p+1;z.KK=LZ(z.K);z.kk=LZ(z.k);if(p>11){z.a="PM"}else{z.a="AM"}z.m=A;z.mm=LZ(A);z.s=t;z.ss=LZ(t);while(x<G.length){J=G.charAt(x);g="";while((G.charAt(x)==J)&&(x<G.length)){g+=G.charAt(x++)}if(z[g]!=null){o=o+z[g]}else{o=o+g}}return o}function _isInteger(d){var c="1234567890";for(var b=0;b<d.length;b++){if(c.indexOf(d.charAt(b))==-1){return false}}return true}function _getInt(g,e,f,d){for(var b=d;b>=f;b--){var c=g.substring(e,e+b);if(c.length<f){return null}if(_isInteger(c)){return c}}return null}function getDateFromFormat(z,q){z=z+"";q=q+"";var w=0;var m=0;var s="";var g="";var v="";var j,h;var d=new Date();var k=d.getYear();var u=d.getMonth()+1;var t=1;var e=d.getHours();var r=d.getMinutes();var o=d.getSeconds();var l="";while(m<q.length){s=q.charAt(m);g="";while((q.charAt(m)==s)&&(m<q.length)){g+=q.charAt(m++)}if(g=="yyyy"||g=="yy"||g=="y"){if(g=="yyyy"){j=4;h=4}if(g=="yy"){j=2;h=2}if(g=="y"){j=2;h=4}k=_getInt(z,w,j,h);if(k==null){return 0}w+=k.length;if(k.length==2){if(k>70){k=1900+(k-0)}else{k=2000+(k-0)}}}else{if(g=="MMM"||g=="NNN"){u=0;for(var p=0;p<MONTH_NAMES.length;p++){var f=MONTH_NAMES[p];if(z.substring(w,w+f.length).toLowerCase()==f.toLowerCase()){if(g=="MMM"||(g=="NNN"&&p>11)){u=p+1;if(u>12){u-=12}w+=f.length;break}}}if((u<1)||(u>12)){return 0}}else{if(g=="EE"||g=="E"){for(var p=0;p<DAY_NAMES.length;p++){var n=DAY_NAMES[p];if(z.substring(w,w+n.length).toLowerCase()==n.toLowerCase()){w+=n.length;break}}}else{if(g=="MM"||g=="M"){u=_getInt(z,w,g.length,2);if(u==null||(u<1)||(u>12)){return 0}w+=u.length}else{if(g=="dd"||g=="d"){t=_getInt(z,w,g.length,2);if(t==null||(t<1)||(t>31)){return 0}w+=t.length}else{if(g=="hh"||g=="h"){e=_getInt(z,w,g.length,2);if(e==null||(e<1)||(e>12)){return 0}w+=e.length}else{if(g=="HH"||g=="H"){e=_getInt(z,w,g.length,2);if(e==null||(e<0)||(e>23)){return 0}w+=e.length}else{if(g=="KK"||g=="K"){e=_getInt(z,w,g.length,2);if(e==null||(e<0)||(e>11)){return 0}w+=e.length}else{if(g=="kk"||g=="k"){e=_getInt(z,w,g.length,2);if(e==null||(e<1)||(e>24)){return 0}w+=e.length;e--}else{if(g=="mm"||g=="m"){r=_getInt(z,w,g.length,2);if(r==null||(r<0)||(r>59)){return 0}w+=r.length}else{if(g=="ss"||g=="s"){o=_getInt(z,w,g.length,2);if(o==null||(o<0)||(o>59)){return 0}w+=o.length}else{if(g=="a"){if(z.substring(w,w+2).toLowerCase()=="am"){l="AM"}else{if(z.substring(w,w+2).toLowerCase()=="pm"){l="PM"}else{return 0}}w+=2}else{if(z.substring(w,w+g.length)!=g){return 0}else{w+=g.length}}}}}}}}}}}}}}if(w!=z.length){return 0}if(u==2){if(((k%4==0)&&(k%100!=0))||(k%400==0)){if(t>29){return 0}}else{if(t>28){return 0}}}if((u==4)||(u==6)||(u==9)||(u==11)){if(t>30){return 0}}if(e<12&&l=="PM"){e=e-0+12}else{if(e>11&&l=="AM"){e-=12}}var b=new Date(k,u-1,t,e,r,o);return b.getTime()}function parseDate(k){var g=(arguments.length==2)?arguments[1]:false;generalFormats=new Array("y-M-d","MMM d, y","MMM d,y","y-MMM-d","d-MMM-y","MMM d");monthFirst=new Array("M/d/y","M-d-y","M.d.y","MMM-d","M/d","M-d");dateFirst=new Array("d/M/y","d-M-y","d.M.y","d-MMM","d/M","d-M");var c=new Array("generalFormats",g?"dateFirst":"monthFirst",g?"monthFirst":"dateFirst");var h=null;for(var f=0;f<c.length;f++){var b=window[c[f]];for(var e=0;e<b.length;e++){h=getDateFromFormat(k,b[e]);if(h!=0){return new Date(h)}}}return null};