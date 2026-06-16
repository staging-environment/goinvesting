@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-slate-800 bg-slate-950/70 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm placeholder-slate-600 focus:outline-none']) }}>

