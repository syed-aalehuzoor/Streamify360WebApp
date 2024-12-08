<button 
    :class="activeTab === {{ $tabId }} ? 'border-b-2 border-secondary text-secondary' : 'text-gray-600'"
    @click="activeTab = {{ $tabId }}" 
    class="flex items-center justify-center gap-x-2 py-2 px-4 focus:outline-none"> 
    <i class="w-5 h-5 {{ $icon }}"></i>
    <span class="text-sm font-medium">{{ $tabName }}</span>
</button>
