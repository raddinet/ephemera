<?php
    include 'php/http.php';
    include 'php/xhtml.php';
    include 'php/crockford32.php';
    include 'cfg.php';
    
    session_start ();
    $mysqli = new mysqli (cfg::localsql, cfg::username, cfg::password, cfg::database);
    
    function screen ($id, $key = 0) {
        global $mysqli;
        
        if ($stmt = $mysqli->prepare ("SELECT `key`=? FROM `keys` WHERE `id`=?")) {
            $stmt->bind_param ('ss', $key, $id);
            if ($stmt->execute()) {
                $stmt->bind_result ($valid);
                
                switch ($stmt->fetch ()) {
                    case NULL: // no such ID
                        return NULL;
                    
                    case TRUE:
                        $stmt->close ();
                        return $valid; // 1 if key is valid, 0 if not
                }
            }
        }
        
        exit;
    }
    
    function exists ($user) {
        $id = crockford32_decode ($user);
        if (screen ($id) !== FALSE) {
            return $id;
        } else
            return FALSE;
    }
    
    function authenticate ($user) {
        global $mysqli;
    
        $user = crockford32_decode ($user);
        $id = substr ($user, 0, 5);
        $key = substr ($user, 5, 5);
        
        switch (screen ($id, $key)) {
            case 1:
                return $id;
            case NULL:
                if ($insert = $mysqli->prepare ("INSERT INTO `keys`(`id`,`key`) VALUES (?,?)")) {
                    $insert->bind_param ('ss', $id, $key);
                    if ($insert->execute ()) {
                        $insert->close ();
                        return $id;
                    }
                }
        }
        return FALSE;
    }

    if (!$mysqli->connect_errno) {
    
        if (isset ($_GET['user'])) {
            if ($id = authenticate ($_GET['user'])) {
                if ($stmt = $mysqli->prepare ("SELECT `t`,`ttl`,`to`,`from`,`text` FROM `data`"
                                             ." WHERE (`to`=? OR `from`=?)"
                                             ."   AND DATE_ADD(`t`,INTERVAL `ttl` MINUTE) >= CURRENT_TIMESTAMP"
                                             ." ORDER BY `t` ASC")) {
                    $stmt->bind_param ('ss', $id, $id);
                    if ($stmt->execute()) {
                        $stmt->bind_result ($t, $ttl, $to, $from, $text);
                        
                        while ($stmt->fetch ()) {
                            if ($from == $id ) {
                                echo '<span class="outbound">';
                            }
                            
                            echo '<span class="time">', $t, '</span> '
                               , '[<span class="from">', crockford32_encode ($from), ' -&gt; ', crockford32_encode ($to), '</span>] '
                               , xhtml::escape ($text) // TODO: nl2br?
                               , '<br />';
                               
                            if ($from == $id ) {
                                echo '</span>';
                            }
                        }
                    }
                }

                if ($stmt = $mysqli->prepare ("DELETE FROM `data`"
                                             ." WHERE `to`=?"
                                             ."   AND DATE_ADD(`t`,INTERVAL `ttl` MINUTE) < CURRENT_TIMESTAMP")) {
                    $stmt->bind_param ('s', $id);
                    $stmt->execute();
                }
            }
        }
    
        if (isset ($_POST['action']))
        switch ($_POST['action']) {
            case 'send':
                if ($id = authenticate ($_POST['user'])) {
                    if ($peer = exists ($_POST['peer'])) {
                        $ttl = intval ($_POST['time']);
                        
                        // insert
                        if ($insert = $mysqli->prepare ("INSERT INTO `data`(`ttl`,`from`,`to`,`text`) VALUES (?,?,?,?)")) {
                            $insert->bind_param ('dsss', $ttl, $id, $peer, $_POST['text']);
                            if ($insert->execute()) {
                                $insert->close();
                                exit;
                            }
                        }
                    } else {
                        echo 'NO PEER';
                    }
                } else {
                    echo 'BAD KEY';
                }
        
                echo 'DB ERROR';
                sleep (3);
                break;
        }
    }
?>
