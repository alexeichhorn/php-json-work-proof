# JSON Work Proof

JSON Work Proof is a proof-of-work algorithm that creates a token after doing some workload. This token contains certain claims defined by you and verifies that you did this work at this time and for these claims.

It packs the security of the Hashcash algorithm *(used for Bitcoin in a similar way)* into a modern JWT-like token.

## Structure of Token

A token looks like this: `eyJ0eXAiOiJKV1AiLCJhbGciOiJTSEEyNTYiLCJkaWYiOjIwfQ.eyJleHAiOjE2MTY4NTA1NzAuNjU1MTQ3MSwiaGVsbG8iOiJ3b3JsZCJ9.VE6YYxIQ46lPzxyNuRYAmAMkEM`. It has the same structure as a JWT token and can therefore also be inspected on the Debugger on [jwt.io](https://jwt.io).
It contains three elements which are each base64url encoded. The header contains the type of the token (JWP), the hash algorithm used for the challenge (currently only SHA256 supported) and the difficulty at which the token was mined. The payload consists of the claims you specified and optionally an expiration date. The last part contains a salt and a big number (named counter in Hashcash). The work needed to generate a token is actually to find this number. It's hard to find this number, but easy to verify it's correct. (Read more about how it works on [Wikipedia](https://en.wikipedia.org/wiki/Hashcash))


## Possible Applications

Can be used to prevent DDOS attacks or as an alternative to rate limiting or captchas. 

E.g. you can use this to prevent brute forcing user logins: The client generates a token with the claims including username and password and sends it along with the login request. The server then first checks if the token is valid before it does any lookup. The scale of bruteforcing can therefore be massively reduced.



## Usage

**Note:** Currently only decoding is supported in PHP.

### General

To generate and validate tokens you need to use a `JWP`-object. On creation you can specify the `difficulty`, which determines how hard the challenge should be. It defaults to `20`, which takes about a second to compute on an average computer. Each increment by one, doubles the difficulty and therefore the time it takes to generate.
```
use alexeichhorn\JSONWorkProof\JWP;

$jwp = new JWP(); # defaults to difficulty 20
$jwp_harder = new JWP(25);
```

### Generation

Currenlty not supported in PHP



### Validation

To check if a token is valid for a certain difficulty and to read the claims:
```
$jwp = new JWP();
try {
  $claims = $jwp->decode($token);
} catch (InvalidFormatException $e) {
  echo "The token is formatted incorrectly";
} catch (InvalidProofException $e) {
  echo "The difficulty this token was created at is lower than what is specified in your JWP object";
} catch (ExpiredException $e) {
  echo "The token expiration is too old or too new";
}
```


If you just want to read the claims without verifying the proof and expiration date, you can use this instead:
```
$claims = jwp.decode(token, false)
```

It expects the expiration date to be between now and 30 minutes in the future.


