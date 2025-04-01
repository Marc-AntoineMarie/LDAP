<?php
	
	
	
	/*// create for tcp
	$sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
	socket_bind($sock, '54.36.189.50',8001);
	socket_listen($sock);
	*/
    
    // create a streaming socket, of type TCP/IP
    $sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
    
    // set the option to reuse the port
    socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
    
    // "bind" the socket to the address to "localhost", on port $port
    // so this means that all connections on this port are now our resposibility to send/recv data, disconnect, etc..
    socket_bind($sock, "54.36.189.50", 8001);
    
    // start listen for connections
    socket_listen($sock);
	
	$clients = array($sock);
    
    while (true) 
	{
        // create a copy, so $clients doesn't get modified by socket_select()
        $read = $clients;
		
		$write = NULL;
		$except = NULL;
        
        // get a list of all the clients that have data to be read from
        // if there are no clients with data, go to next iteration
		$test = socket_select($read, $write, $except, 0);
		
		//echo "Echec socket_select: [$errorcode] $errormsg";
		if ($test < 1) continue;
        
        // check if there is a client trying to connect
        if (in_array($sock, $read)) 
		{
            // accept the client, and add him to the $clients array
            $clients[] = $newsock = socket_accept($sock);
            
			//------------ Envoi GetParameters ------------------
			
			$body = "<soap:Envelope
                xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\"
                xmlns:soap-enc=\"http://schemas.xmlsoap.org/soap/encoding/\"
                xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"
                xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
                xmlns:cwmp=\"urn:dslforum-org:cwmp-1-0\">
                <soap:Header>
                    <cwmp:ID soap:mustUnderstand=\"1\">0</cwmp:ID>
                </soap:Header>
                <soap:Body>";
			$body .= "<cwmp:InformResponse>";
			$body .= "</cwmp:InformResponse>";
			$body .= " </soap:Body>
					</soap:Envelope>";
			
			$header = "POST / HTTP/1.1\r\n
				Host: 54.36.189.50:8001\r\n
				User-Agent: ACS GC\r\n
				Content-Type: text/xml; charset=utf-8\r\n
				SOAPAction: cwmp:InformResponse\r\n	
				Content-Length: ".strlen($body)."\r\n
				\r\n";
				
			$txt = $body.$header;
			
					
					
			/*
			
				$txt .= "<cwmp:GetParameterNames>";
			$txt .= "<ParameterPath>InternetGatewayDevice.DeviceInfo.MACAddress</ParameterPath>";
			$txt .= "<NextLevel>false</NextLevel>";
			$txt .= "</cwmp:GetParameterNames>";
			
			*/

            // send the client a welcome message
            socket_write($newsock, $txt, strlen($txt));
            
            socket_getpeername($newsock, $ip);
            echo "New client connected: {$ip}<BR>";
            
            // remove the listening socket from the clients-with-data array
            $key = array_search($sock, $read);
            unset($read[$key]);
        }
        
        // loop through all the clients that have data to read from
        foreach ($read as $read_sock) 
		{
            // read until newline or 1024 bytes
            // socket_read while show errors when the client is disconnected, so silence the error messages
            $data = socket_read($read_sock, 1024, PHP_NORMAL_READ);
			
			echo date("Y-m-d H:i:s")." : $data<BR>";
			
            
            // check if the client is disconnected
            if ($data === false) 
			{
                // remove client for $clients array
                $key = array_search($read_sock, $clients);
                unset($clients[$key]);
                echo "client disconnected.<BR>";
                // continue to the next client to read from, if any
                continue;
            }
            
            // trim off the trailing/beginning white spaces
            $data = trim($data);
            
            // check if there is any data after trimming off the spaces
            if (!empty($data)) 
			{
                // send this to all the clients in the $clients array (except the first one, which is a listening socket)
                foreach ($clients as $send_sock) 
				{
					
                    // if its the listening sock or the client that we got the message from, go to the next one in the list
                    if ($send_sock == $sock || $send_sock == $read_sock)
                        continue;
					
                    // write the message to the client -- add a newline character to the end of the message
                    socket_write($send_sock, "TEST !!!!\n");
                    
                } // end of broadcast foreach
                
            }
            
        } // end of reading foreach
		
    }
	
	echo "fin";

    // close the listening socket
    socket_close($sock);
	
?>