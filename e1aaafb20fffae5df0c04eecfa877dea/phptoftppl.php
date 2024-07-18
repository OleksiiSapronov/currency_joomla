  <?

 // $serwer="ftp://ftp@avsh.pl:K(0)ral0wieC@podroze.home.pl/f/";

//

 // $plikk="full_php_browscap.ini";

$ftp_server = "podroze.home.pl";
$conn_id = ftp_connect ($ftp_server)
    or die("Couldn't connect to $ftp_server");
   
$login_result = ftp_login($conn_id, "ftp@avsh.pl", "K(0)ral0wieC");
if ((!$conn_id) || (!$login_result))
    die("FTP Connection Failed");

  
ftp_sync ("../");    // Use "." if you are in the current directory

ftp_close($conn_id);  

 // ftp_sync - Copy directory and file structure
function ftp_sync ($dir) {

    global $conn_id;

    if ($dir != ".") {
        if (ftp_chdir($conn_id, $dir) == false) {
            echo ("Change Dir Failed: $dir<BR>\r\n");
            return;
        }
        if (!(is_dir($dir)))
            mkdir($dir);
        chdir ($dir);
    }

    $contents = ftp_nlist($conn_id, ".");
    foreach ($contents as $file) {
   
        if ($file == '.' || $file == '..')
            continue;
       
        if (@ftp_chdir($conn_id, $file)) {
            ftp_chdir ($conn_id, "..");
            ftp_sync ($file);
        }
        else
            ftp_get($conn_id, $file, $file, FTP_BINARY);
    }
       
    ftp_chdir ($conn_id, "..//");
    chdir ("..");

}  

 ?>                                  