<button {{ $attributes->merge(['type' => 'submit', 'class' => 'rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
