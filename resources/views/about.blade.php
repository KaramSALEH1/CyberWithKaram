@extends('layouts.app')
@section('title', 'About CyberLogia')

@section('content')
    <div class="min-h-screen bg-[#050505] text-gray-100">
        <!-- Hero -->
        <section class="relative py-24 px-6 lg:px-8 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-[32rem] h-[32rem] rounded-full bg-karam-green/5 blur-3xl"></div>
            <div class="max-w-5xl mx-auto relative">
                <span class="inline-block font-mono text-[11px] uppercase tracking-[0.35em] text-karam-green mb-6">
                    About the platform
                </span>

                <h1 class="text-4xl md:text-6xl font-black tracking-tight leading-[1.1] mb-6">
                    <span class="text-white">Cyber</span><span class="text-karam-green">Logia</span>
                </h1>

                <p class="text-xl md:text-2xl text-gray-300 max-w-3xl leading-relaxed mb-6">
                    Continuous, automated cybersecurity for enterprises, banks and SMEs —
                    delivered as self-installing agents rather than annual consulting engagements.
                </p>

                <p class="text-gray-400 max-w-3xl leading-relaxed">
                    CyberLogia is an advanced automated cybersecurity SaaS platform. Every capability we
                    sell ships as a production-grade Python agent that installs in minutes, runs continuously
                    on your infrastructure, and reports back the moment something changes.
                </p>
            </div>
        </section>

        <!-- Three pillars -->
        <section class="py-16 px-6 lg:px-8 bg-gray-900/40 border-y border-white/5">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-3xl md:text-4xl font-extrabold mb-3">Three agent families. One platform.</h2>
                    <p class="text-gray-500 max-w-2xl mx-auto">
                        Detect, attack and defend — continuously, with evidence you can hand to an auditor.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="group bg-[#0a0a0a] border border-white/5 hover:border-blue-400/40 rounded-2xl p-8 transition-all duration-300">
                        <div class="text-4xl mb-5">🛡️</div>
                        <h3 class="text-xl font-bold mb-3 text-blue-300">Blue Team</h3>
                        <p class="text-gray-400 leading-relaxed mb-5 text-sm">
                            Continuous detection and hardening. Automated EDR &amp; threat hunting, CIS
                            Benchmark compliance, log collection with SIEM forwarding, and file integrity
                            monitoring.
                        </p>
                        <span class="font-mono text-xs text-blue-400">4 agents</span>
                    </div>

                    <div class="group bg-[#0a0a0a] border border-white/5 hover:border-red-400/40 rounded-2xl p-8 transition-all duration-300">
                        <div class="text-4xl mb-5">🎯</div>
                        <h3 class="text-xl font-bold mb-3 text-red-300">Red Team</h3>
                        <p class="text-gray-400 leading-relaxed mb-5 text-sm">
                            Authorised offensive validation. Non-destructive ransomware &amp; breach
                            simulation, internal attack-surface scanning, and static application
                            misconfiguration auditing.
                        </p>
                        <span class="font-mono text-xs text-red-400">3 agents</span>
                    </div>

                    <div class="group bg-[#0a0a0a] border border-white/5 hover:border-sky-400/40 rounded-2xl p-8 transition-all duration-300">
                        <div class="text-4xl mb-5">☁️</div>
                        <h3 class="text-xl font-bold mb-3 text-sky-300">Cloud Security</h3>
                        <p class="text-gray-400 leading-relaxed mb-5 text-sm">
                            Cloud-native defence. Virtual sandbox detonation for email attachments,
                            CIS Cloud Foundations benchmarking, and Kubernetes &amp; Docker
                            container security auditing.
                        </p>
                        <span class="font-mono text-xs text-sky-400">3 agents</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section class="py-20 px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-extrabold mb-5">
                            From purchase to protection in <span class="text-karam-green">four steps</span>
                        </h2>
                        <p class="text-gray-400 leading-relaxed mb-8">
                            No SIEM to build. No agents to fleet-manage. No consultants on site.
                            Subscribe, download, run — and the agent keeps working.
                        </p>

                        @php
                            $steps = [
                                ['Subscribe', 'Pick the agent you need and pay monthly in SYP.'],
                                ['Get your licence', 'A high-entropy licence key is issued to your workspace within 24 hours.'],
                                ['Download &amp; run', 'One cross-platform file: agent_bootstrapper.py.'],
                                ['Stay protected', 'It fetches its workload, executes it, and heartbeats every 60 seconds.'],
                            ];
                        @endphp

                        <ol class="space-y-5">
                            @foreach ($steps as $index => [$title, $body])
                                <li class="flex gap-4">
                                    <span
                                        class="flex-shrink-0 w-8 h-8 rounded-full bg-karam-green/10 border border-karam-green/30 text-karam-green font-mono font-bold flex items-center justify-center text-sm">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <h3 class="font-semibold mb-1">{!! $title !!}</h3>
                                        <p class="text-gray-400 text-sm leading-relaxed">{!! $body !!}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="bg-[#0a0a0a] border border-white/5 rounded-2xl p-2">
                        <div
                            class="rounded-xl bg-gray-950 border border-white/5 p-8 flex flex-col items-center justify-center min-h-[22rem]">
                            <img src="{{ asset('images/logo.png') }}" alt="CyberLogia"
                                class="h-40 w-auto max-w-full object-contain opacity-80 drop-shadow-[0_0_35px_rgba(0,242,96,0.25)]">
                            <p class="font-mono text-xs text-karam-green tracking-[0.3em] uppercase mt-8">
                                Secure Today · Protect Tomorrow
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-20 px-6 lg:px-8 bg-gray-900/40 border-t border-white/5">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-extrabold mb-4">Ready to automate your defence?</h2>
                <p class="text-gray-400 mb-8">
                    Browse the full matrix of automated agents and deploy your first one today.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('services') }}"
                        class="px-10 py-4 rounded-xl bg-karam-green text-black font-bold hover:opacity-90 transition shadow-[0_0_30px_rgba(0,242,96,0.25)]">
                        Explore Services
                    </a>
                    <a href="{{ route('contact') }}"
                        class="px-10 py-4 rounded-xl border border-gray-700 hover:border-karam-green font-bold transition">
                        Talk to Us
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
