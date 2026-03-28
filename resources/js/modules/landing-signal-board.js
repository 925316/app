export default function registerLandingSignalBoard(Alpine) {
    Alpine.data('landingSignalBoard', () => ({
        config: {
            startDate: '2026-03-28',
            activeLicenses: {
                base: 2314,
                maxDailyGrowth: 9,
                seed: 'active-licenses',
            },
            boundDevices: {
                base: 1847,
                maxDailyGrowth: 5,
                seed: 'bound-devices',
            },
            issuedSeats: {
                base: 2755,
                maxDailyGrowth: 8,
                seed: 'issued-seats',
            },
            deploySuccess: {
                base: 99.2,
                swing: 0.4,
                stepSeconds: 3,
                seed: 'deploy-success',
            },
        },
        stats: {
            activeLicenses: {
                value: 2314,
                todayChange: 0,
                projectedChange: 0,
            },
            boundDevices: {
                value: 9847,
                todayChange: 0,
                projectedChange: 0,
            },
            coverageRate: {
                value: 84.0,
                issuedSeats: 2755,
            },
            deploySuccess: {
                value: 99.2,
                variation: 0,
                direction: 'steady',
            },
        },
        animated: {
            activeLicenses: 2314,
            boundDevices: 1847,
            coverageRate: 84.0,
            deploySuccess: 99.2,
        },
        reducedMotion: false,
        counterIntervalId: null,
        deployIntervalId: null,
        deployTimeoutId: null,
        init() {
            this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            this.refreshBoard({
                animate: !this.reducedMotion,
                prime: true,
            });

            if (!this.reducedMotion) {
                this.counterIntervalId = window.setInterval(() => {
                    this.refreshCounters();
                }, 15000);

                this.scheduleDeployRefresh();
            }

            window.addEventListener('beforeunload', () => {
                this.clearTimers();
            }, {
                once: true,
            });
        },
        clearTimers() {
            if (this.deployTimeoutId) {
                window.clearTimeout(this.deployTimeoutId);
            }

            if (this.deployIntervalId) {
                window.clearInterval(this.deployIntervalId);
            }

            if (this.counterIntervalId) {
                window.clearInterval(this.counterIntervalId);
            }
        },
        refreshBoard({ animate = false, prime = false } = {}) {
            const snapshot = this.computeSnapshot(new Date());
            this.stats = snapshot;

            if (prime) {
                this.animated.activeLicenses = snapshot.activeLicenses.value;
                this.animated.boundDevices = snapshot.boundDevices.value;
                this.animated.coverageRate = snapshot.coverageRate.value;
                this.animated.deploySuccess = snapshot.deploySuccess.value;
            }

            if (animate) {
                this.animated.activeLicenses = Math.max(0, snapshot.activeLicenses.value - Math.max(12, snapshot.activeLicenses.projectedChange + 4));
                this.animated.boundDevices = Math.max(0, snapshot.boundDevices.value - Math.max(6, snapshot.boundDevices.projectedChange + 2));
                this.animated.coverageRate = Math.max(0, snapshot.coverageRate.value - 1.2);
                this.animated.deploySuccess = Math.max(0, snapshot.deploySuccess.value - 0.5);

                window.requestAnimationFrame(() => {
                    this.animateValue('activeLicenses', snapshot.activeLicenses.value, 1100);
                    this.animateValue('boundDevices', snapshot.boundDevices.value, 980);
                    this.animateValue('coverageRate', snapshot.coverageRate.value, 960);
                    this.animateValue('deploySuccess', snapshot.deploySuccess.value, 900);
                });
            }
        },
        refreshCounters() {
            const snapshot = this.computeSnapshot(new Date());

            this.stats.activeLicenses = snapshot.activeLicenses;
            this.stats.boundDevices = snapshot.boundDevices;
            this.stats.coverageRate = snapshot.coverageRate;

            this.animateValue('activeLicenses', snapshot.activeLicenses.value, 700);
            this.animateValue('boundDevices', snapshot.boundDevices.value, 700);
            this.animateValue('coverageRate', snapshot.coverageRate.value, 700);
        },
        scheduleDeployRefresh() {
            const stepMilliseconds = this.config.deploySuccess.stepSeconds * 1000;
            const now = Date.now();
            const waitMilliseconds = stepMilliseconds - (now % stepMilliseconds);

            this.deployTimeoutId = window.setTimeout(() => {
                this.refreshDeploySuccess();
                this.deployIntervalId = window.setInterval(() => {
                    this.refreshDeploySuccess();
                }, stepMilliseconds);
            }, waitMilliseconds);
        },
        refreshDeploySuccess() {
            const deploySuccess = this.computeDeploySuccess(new Date());
            this.stats.deploySuccess = deploySuccess;
            this.animateValue('deploySuccess', deploySuccess.value, 900);
        },
        computeSnapshot(now) {
            const activeLicenses = this.computeCountStat(this.config.activeLicenses, now);
            const boundDevices = this.computeCountStat(this.config.boundDevices, now);
            const issuedSeats = this.computeCountStat(this.config.issuedSeats, now);

            return {
                activeLicenses,
                boundDevices,
                coverageRate: {
                    value: Number(((activeLicenses.value / Math.max(1, issuedSeats.value)) * 100).toFixed(1)),
                    issuedSeats: issuedSeats.value,
                },
                deploySuccess: this.computeDeploySuccess(now),
            };
        },
        computeCountStat(statConfig, now) {
            const startDate = this.startOfDay(new Date(`${this.config.startDate}T00:00:00`));
            const currentDate = this.startOfDay(now);
            const dayIndex = Math.max(0, Math.floor((currentDate.getTime() - startDate.getTime()) / 86400000));
            let value = statConfig.base;
            let projectedChange = 0;
            let todayChange = 0;

            for (let completedDay = 1; completedDay < dayIndex; completedDay += 1) {
                value += this.dayGrowth(statConfig.seed, completedDay, statConfig.maxDailyGrowth);
            }

            if (dayIndex > 0) {
                projectedChange = this.dayGrowth(statConfig.seed, dayIndex, statConfig.maxDailyGrowth);
                todayChange = Math.floor(projectedChange * this.dayProgress(now));
                value += todayChange;
            }

            return {
                value,
                todayChange,
                projectedChange,
            };
        },
        computeDeploySuccess(now) {
            const bucketIndex = Math.floor(now.getTime() / 1000 / this.config.deploySuccess.stepSeconds);
            const swingSteps = Math.max(1, Math.round(this.config.deploySuccess.swing * 10));
            const variationSteps = (this.stableHash(this.config.deploySuccess.seed, bucketIndex) % ((swingSteps * 2) + 1)) - swingSteps;
            const variation = Number((variationSteps / 10).toFixed(1));
            const value = Number(Math.min(100, Math.max(0, this.config.deploySuccess.base + variation)).toFixed(1));

            return {
                value,
                variation,
                direction: variation > 0 ? 'up' : (variation < 0 ? 'down' : 'steady'),
            };
        },
        dayGrowth(seed, dayIndex, maxDailyGrowth) {
            return this.stableHash(seed, dayIndex) % (maxDailyGrowth + 1);
        },
        dayProgress(now) {
            const secondsIntoDay = (now.getHours() * 3600) + (now.getMinutes() * 60) + now.getSeconds();

            return secondsIntoDay / 86399;
        },
        startOfDay(date) {
            return new Date(date.getFullYear(), date.getMonth(), date.getDate());
        },
        stableHash(seed, index) {
            const source = `${seed}:${index}`;
            let hash = 2166136261;

            Array.from(source).forEach((character) => {
                hash ^= character.codePointAt(0);
                hash = Math.imul(hash, 16777619);
            });

            return hash >>> 0;
        },
        animateValue(key, target, duration = 900) {
            const start = this.animated[key];
            const startTime = performance.now();
            const isWholeNumber = Number.isInteger(target);

            const step = (currentTime) => {
                const progress = Math.min(1, (currentTime - startTime) / duration);
                const easedProgress = 1 - ((1 - progress) ** 3);
                const nextValue = start + ((target - start) * easedProgress);

                this.animated[key] = isWholeNumber ? Math.round(nextValue) : Number(nextValue.toFixed(1));

                if (progress < 1) {
                    window.requestAnimationFrame(step);

                    return;
                }

                this.animated[key] = target;
            };

            window.requestAnimationFrame(step);
        },
        formatNumber(value) {
            return new Intl.NumberFormat().format(Math.round(value));
        },
        formatPercent(value, precision = 1) {
            return `${Number(value).toFixed(precision)}%`;
        },
        launchLabel() {
            return new Intl.DateTimeFormat(undefined, {
                month: 'short',
                day: '2-digit',
                year: 'numeric',
            }).format(new Date(`${this.config.startDate}T00:00:00`));
        },
        driftLabel() {
            const variation = Number(this.stats.deploySuccess.variation).toFixed(1);

            return `${variation > 0 ? '+' : ''}${variation}% drift`;
        },
        deployRefreshLabel() {
            if (this.reducedMotion) {
                return 'Static when reduced motion is enabled';
            }

            return `Refreshes every ${this.config.deploySuccess.stepSeconds}s`;
        },
    }));
}
