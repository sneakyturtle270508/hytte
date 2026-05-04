#!/usr/bin/env node

'use strict';

const http = require('http');
const https = require('https');
const { performance } = require('perf_hooks');

const baseUrl = process.env.BASE_URL || 'http://localhost:8000';
const users = Number.parseInt(process.env.USERS || '1000', 10);
const requestsPerUser = Number.parseInt(process.env.REQUESTS_PER_USER || '3', 10);
const concurrency = Number.parseInt(process.env.CONCURRENCY || '50', 10);
const timeoutMs = Number.parseInt(process.env.TIMEOUT_MS || '10000', 10);

const paths = [
	'/',
	'/cabins',
	'/cabins/fjord-retreat-0001',
	'/cabins/forest-retreat-0002',
	'/cabins/mountain-retreat-0003'
];

function request(url) {
	return new Promise((resolve) => {
		const started = performance.now();
		const client = url.protocol === 'https:' ? https : http;
		const req = client.get(url, { timeout: timeoutMs }, (res) => {
			res.resume();
			res.on('end', () => {
				resolve({
					status: res.statusCode,
					ms: performance.now() - started
				});
			});
		});

		req.on('timeout', () => {
			req.destroy(new Error('timeout'));
		});

		req.on('error', (error) => {
			resolve({
				status: 0,
				error: error.message,
				ms: performance.now() - started
			});
		});
	});
}

function percentile(values, p) {
	if (!values.length) {
		return 0;
	}
	const sorted = [...values].sort((a, b) => a - b);
	const index = Math.ceil((p / 100) * sorted.length) - 1;
	return sorted[Math.max(0, Math.min(index, sorted.length - 1))];
}

async function main() {
	const totalRequests = users * requestsPerUser;
	let next = 0;
	const results = [];
	const started = performance.now();

	console.log(`Load test: ${baseUrl}`);
	console.log(`Users: ${users}, requests/user: ${requestsPerUser}, total requests: ${totalRequests}, concurrency: ${concurrency}`);

	async function worker() {
		while (next < totalRequests) {
			const id = next++;
			const path = paths[id % paths.length];
			const url = new URL(path, baseUrl);
			results.push(await request(url));
		}
	}

	await Promise.all(Array.from({ length: concurrency }, worker));

	const elapsed = (performance.now() - started) / 1000;
	const durations = results.map((result) => result.ms);
	const failures = results.filter((result) => result.status < 200 || result.status >= 400).length;
	const byStatus = results.reduce((acc, result) => {
		acc[result.status] = (acc[result.status] || 0) + 1;
		return acc;
	}, {});

	console.log('\nResults');
	console.log(`Elapsed: ${elapsed.toFixed(2)}s`);
	console.log(`Requests/sec: ${(results.length / elapsed).toFixed(2)}`);
	console.log(`Failures: ${failures}`);
	console.log(`Status counts: ${JSON.stringify(byStatus)}`);
	console.log(`Avg: ${(durations.reduce((sum, ms) => sum + ms, 0) / durations.length).toFixed(1)}ms`);
	console.log(`P50: ${percentile(durations, 50).toFixed(1)}ms`);
	console.log(`P95: ${percentile(durations, 95).toFixed(1)}ms`);
	console.log(`P99: ${percentile(durations, 99).toFixed(1)}ms`);

	if (failures > 0) {
		process.exitCode = 1;
	}
}

main().catch((error) => {
	console.error(error);
	process.exit(1);
});
