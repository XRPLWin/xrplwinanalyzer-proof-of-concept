# Varnish version 6.1.x
# 4.0 or 4.1 syntax.
vcl 4.1;
import std;

# Default backend definition. Set this to point to your content server.
backend analyzerxrplwin {
  .host = "127.0.0.1";
  .port = "8080";
	.max_connections = 4096;

  .probe = {
      #.url = "/"; # short easy way (GET /)
      # We prefer to only do a HEAD /
      .request =
        "HEAD / HTTP/1.1"
        "Host: localhost"
        "Connection: close"
        "User-Agent: Varnish Health Probe";
      .interval  = 15s; # check the health of each backend every 15 seconds
      .timeout   = 1s; # timing out after 1 second.
      .window    = 5;  # If 3 out of the last 5 polls succeeded the backend is considered healthy, otherwise it will be marked as sick
      .threshold = 3;
    }
	.first_byte_timeout     = 60s;   # How long to wait before we receive a first byte from our backend?
	.connect_timeout        = 5s;    # How long to wait for a backend connection?
	.between_bytes_timeout  = 2s;    # How long to wait between bytes received from our backend?
}

sub vcl_recv {
  if (req.http.host == "analyzer.xrpl.win") {
    set req.backend_hint = analyzerxrplwin;
    unset req.http.x-cache;

    # Normalize the query arguments
    set req.url = std.querysort(req.url);

    # Strip hash, server doesn't need it.
    if (req.url ~ "\#") {
      set req.url = regsub(req.url, "\#.*$", "");
    }

    # Happens before we check if we have this in cache already.
    #
    # Typically you clean up the request here, removing cookies you don't need,
    # rewriting the request, etc.
    #return(pass);

    # Only deal with "normal" types
    if (req.method != "GET" &&
        req.method != "HEAD" &&
        req.method != "PUT" &&
        req.method != "POST" &&
        req.method != "TRACE" &&
        req.method != "OPTIONS" &&
        req.method != "PATCH" &&
        req.method != "DELETE") {
      /* Non-RFC2616 or CONNECT which is weird. */
      return (pipe);
    }

    if (req.http.Upgrade ~ "(?i)websocket") {
      return (pipe);
    }

    if (req.method == "POST") {
      return (pass);
    }

    if (req.method != "GET" && req.method != "HEAD") {
      return (pass);
    }
    # normalize accept-encoding
    if (req.http.Accept-Encoding) {
      if (req.url ~ "\.(jpg|png|gif|gz|tgz|bz2|tbz|mp3|ogg)$") {
        # No point in compressing these
        unset req.http.Accept-Encoding;
      } elsif (req.http.Accept-Encoding ~ "gzip") {
        set req.http.Accept-Encoding = "gzip";
      } elsif (req.http.Accept-Encoding ~ "deflate") {
        set req.http.Accept-Encoding = "deflate";
      } else {
        # unknown algorithm
        unset req.http.Accept-Encoding;
      }
    }

    #ignore all GET parameters for generated jpg images
    if (req.url ~ "\.jpg?.*") {
      set req.url = regsub(req.url, "\.jpg?.*", "\.jpg");
    }

    if (req.url ~ "\.(css|js|gif|jpe?g|bmp|png|tiff?|ico|img|tga|wmf|svg|swf|ico|ttf|eot|wof)$") {
      unset req.http.Cookie;
      return (hash);
    }

    # Large static files are delivered directly to the end-user without, added connection close to vcl_pipe
    # waiting for Varnish to fully read the file first.
    # Varnish 4 fully supports Streaming, so set do_stream in vcl_backend_response()
    # http://jeremiahsturgill.com/255/varnish-pipe-for-large-files/
    if (req.url ~ "^[^?]*\.(7z|avi|bz2|flac|flv|gz|mka|mkv|mov|mp3|mp4|mpeg|mpg|ogg|ogm|opus|rar|tar|tgz|tbz|txz|wav|webm|xz|zip)(\?.*)?$") {
       return(pipe);
    }

    if (req.http.Authenticate || req.http.Authorization) {
      # Not cacheable by default
      return (pass);
    }
    return (hash);
  }
}

sub vcl_backend_response {
    if (bereq.http.host == "analyzer.xrpl.win") {
	    if (bereq.url ~ "\.(css|js|gif|jpe?g|bmp|png|tiff?|ico|img|tga|wmf|svg|swf|ico|ttf|eot|wof)$") {
	      set beresp.ttl = 1y;
	    }
	}
}

sub vcl_hash {
  if (req.http.host == "analyzer.xrpl.win") {
    if(req.http.X-Requested-With == "XMLHttpRequest") {
      hash_data(req.http.X-Requested-With);
    }
    return (lookup); # stop and execute lookup operation
  }
}

sub vcl_pipe {
  if (req.http.host == "analyzer.xrpl.win") {
    if (req.http.upgrade) {
      set bereq.http.upgrade = req.http.upgrade;
    }
    return (pipe);
  }
}
