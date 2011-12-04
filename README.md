#Ananke
Ananke is another little internal project of mine from CFSAS. This one took me about a day--it's a (very basic) analytics tool written on top of CakePHP. We wanted to know the *distribution* of time spent watching various course videos, or reading various material, and couldn't figure out how to get that from Google Analytics. (If it's possible, please let me know!)

Ananke accepts a JSON array POSTed to /visits/triage (where each element is an object that contains a timestamp and an event tag) and calculates the time spent on each event based on the difference between the event timestamps:

```javascript
{[
	{"time":<seconds since UNIX epoch>,"hash":<event identifier>}
	,{"time":<seconds since UNIX epoch>,"hash":<event identifier>}
	.
	.
	.
	,{"time":<seconds since UNIX epoch>,"hash":<event identifier>}
]}
```

Some contingencies are in place for missed communications. 
