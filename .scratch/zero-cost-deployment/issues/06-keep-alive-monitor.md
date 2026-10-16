# 06: Keep-alive monitor

**What to build:** Friends never wait out a 30–60 second cold start, and the free service is never suspended mid-month for overuse — a free monitor pings the live app every ten minutes, inside Render's monthly free-hours budget.

**Blocked by:** 04 (the live URL exists)

**Status:** ready-for-human

- [ ] UptimeRobot HTTPS monitor configured against the live URL, interval 10 minutes
- [ ] Monthly hours budget checked: ~720 hours always-on under keep-alive vs Render's 750 free hours — headroom confirmed
- [ ] Monitor shown green in the UptimeRobot dashboard
