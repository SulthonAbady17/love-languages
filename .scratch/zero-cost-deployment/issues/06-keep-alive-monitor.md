# 06: Keep-alive monitor

**What to build:** Friends never wait out a long cold start — a free monitor pings the live app every ten minutes, inside the free host's budget (Railway credit / Render hours — whichever is live).

**Blocked by:** 04 (the live URL exists)

**Status:** ready-for-human

- [ ] UptimeRobot HTTPS monitor configured against the live URL (`https://love-languages.up.railway.app` after the Railway retarget), interval 10 minutes
- [ ] Free-tier budget checked: keep-alive pings must stay inside Railway's trial/Free credit (or Render hours if ever retargeted back) — enable **scale-to-zero on idle** if credit burns too fast
- [ ] Monitor shown green in the UptimeRobot dashboard
