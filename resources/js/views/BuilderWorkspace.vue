<template>
  <div class="h-screen flex flex-col bg-gray-100 overflow-hidden">
    <!-- Topbar -->
    <header class="bg-white shadow flex justify-between items-center px-6 py-3 z-10 shrink-0">
      <div class="flex items-center">
        <router-link :to="{ name: 'builder' }" class="text-gray-500 hover:text-gray-700 mr-4">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </router-link>
        <h1 class="text-xl font-bold text-gray-900">
            Editing: {{ section?.label || 'Loading...' }}
        </h1>
        <span v-if="section" class="ml-4 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 uppercase">
            {{ section.type }}
        </span>
      </div>
      <div class="flex items-center space-x-4">
        <!-- Edit mode toggle -->
        <div class="inline-flex rounded-md shadow-sm" role="group">
          <button
            type="button"
            @click="editMode = 'inline'"
            class="px-3 py-1.5 text-xs font-medium border"
            :class="editMode === 'inline' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
          >Inline</button>
          <button
            type="button"
            @click="editMode = 'form'"
            class="px-3 py-1.5 text-xs font-medium border-t border-b border-r rounded-r-md"
            :class="editMode === 'form' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
          >Form</button>
        </div>

        <!-- Versions Dropdown / History -->
        <button @click="showVersions = !showVersions" class="text-sm font-medium text-gray-600 hover:text-gray-900">
            Version History
        </button>

        <button @click="saveDraft" :disabled="saving" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
          {{ saving ? 'Saving...' : 'Save Draft' }}
        </button>

        <router-link
          :to="{ name: 'publish' }"
          class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          title="Saving only writes to draft. Publish to make changes visible on the public site."
        >
          Publish
          <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </router-link>
      </div>
    </header>

    <!-- Status banner — explains the draft → publish workflow -->
    <div v-if="section && !loading" class="bg-amber-50 border-b border-amber-200 px-6 py-2 text-xs text-amber-900 flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-2">
            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>
                Saving here only writes to <strong>draft</strong>. To make changes visible on the public site, go to
                <router-link :to="{ name: 'publish' }" class="underline font-semibold">Publish</router-link>
                and click <strong>Publish to Live</strong>.
            </span>
        </div>
        <span class="text-amber-700">
            Current status:
            <span class="font-semibold uppercase">{{ section.status }}</span>
        </span>
    </div>

    <!-- Main Workspace -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- Versions Sidebar Overlay -->
        <div v-show="showVersions" class="w-64 bg-white border-r border-gray-200 flex flex-col z-20 absolute h-full shadow-lg">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-medium text-gray-900">History</h3>
                <button @click="showVersions = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto">
                <ul class="divide-y divide-gray-100">
                    <li v-for="v in versions" :key="v.id" class="p-4 hover:bg-gray-50">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">{{ new Date(v.created_at).toLocaleString() }}</span>
                            <button @click="rollback(v.id)" class="text-xs text-indigo-600 hover:text-indigo-900">Restore</button>
                        </div>
                    </li>
                    <li v-if="versions.length === 0" class="p-4 text-sm text-gray-500 text-center">No history yet.</li>
                </ul>
            </div>
        </div>

        <!-- Settings Sidebar (Left) - only in form mode -->
        <aside v-if="editMode === 'form'" class="w-80 bg-white border-r border-gray-200 overflow-y-auto p-4 shrink-0 transition-all">
            <div v-if="loading" class="text-center py-10 text-gray-500">Loading editor...</div>
            <div v-else>
                <!-- Dynamic Form Config based on section.type -->
                
                <!-- Example: Hero -->
                <div v-if="section.type === 'hero'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Headline</label>
                        <input type="text" v-model="draftContent.headline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Subheadline / Text</label>
                        <textarea v-model="draftContent.text" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Background Image</label>
                        <div v-if="draftContent.bg_image" class="relative mb-2">
                            <img :src="draftContent.bg_image.url" class="h-32 w-full object-cover rounded">
                            <button @click="draftContent.bg_image = null" class="absolute top-1 right-1 bg-red-600 text-white p-1 rounded-full text-xs">Remove</button>
                        </div>
                        <button @click="openMediaPicker('bg_image')" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Select Image
                        </button>
                    </div>
                </div>

                <!-- Example: Text -->
                <div v-else-if="section.type === 'text'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                        <textarea v-model="draftContent.body" rows="10" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Accepts HTML</p>
                    </div>
                </div>

                <!-- Example: Image + Text -->
                <div v-else-if="section.type === 'image_text'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Headline</label>
                        <input type="text" v-model="draftContent.headline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Text Summary</label>
                        <textarea v-model="draftContent.text" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Image</label>
                        <div v-if="draftContent.image" class="relative mb-2">
                            <img :src="draftContent.image.url" class="h-32 w-full object-cover rounded">
                            <button @click="draftContent.image = null" class="absolute top-1 right-1 bg-red-600 text-white p-1 rounded-full text-xs">Remove</button>
                        </div>
                        <button @click="openMediaPicker('image')" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Select Image
                        </button>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Image Position</label>
                        <select v-model="draftContent.image_position" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                </div>

                <!-- Example: Gallery -->
                <div v-else-if="section.type === 'gallery'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Gallery Headline</label>
                        <input type="text" v-model="draftContent.headline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gallery Images</label>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div v-for="(img, idx) in draftContent.images || []" :key="idx" class="relative group">
                                <img :src="img.url" class="h-20 w-full object-cover rounded shadow-sm border border-gray-200">
                                <button @click="draftContent.images.splice(idx, 1)" class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full p-0.5 shadow hover:bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                        <button @click="openMediaPicker('images_array')" class="w-full inline-flex justify-center items-center px-4 py-2 border border-dashed border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-600 bg-gray-50 hover:bg-gray-100 mb-2">
                           + Add Image
                        </button>
                    </div>
                </div>

                <!-- Example: Tabs / Accordion (Shared Item List Logic) -->
                <div v-else-if="section.type === 'tabs' || section.type === 'accordion'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Headline</label>
                        <input type="text" v-model="draftContent.headline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="space-y-4">
                        <div v-for="(item, idx) in draftContent.items" :key="idx" class="p-4 border border-gray-100 rounded-md bg-gray-50 relative">
                            <button @click="draftContent.items.splice(idx, 1)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">&times;</button>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1">Item Title</label>
                                    <input type="text" v-model="item.title" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1">Content (HTML)</label>
                                    <textarea v-model="item.content" rows="4" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <button @click="draftContent.items.push({ title: 'New Item', content: '' })" class="w-full py-2 border border-dashed border-indigo-300 rounded text-sm text-indigo-600 hover:bg-indigo-50 transition-colors">
                            + Add Entry
                        </button>
                    </div>
                </div>
                
                <!-- CTA -->
                <div v-else-if="section.type === 'cta'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Headline</label>
                        <input type="text" v-model="draftContent.headline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Subheadline</label>
                        <input type="text" v-model="draftContent.subheadline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Primary label</label>
                            <input type="text" v-model="draftContent.primary_label" class="mt-1 block w-full border border-gray-300 rounded-md py-1.5 px-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Primary link</label>
                            <input type="text" v-model="draftContent.primary_link" class="mt-1 block w-full border border-gray-300 rounded-md py-1.5 px-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Secondary label</label>
                            <input type="text" v-model="draftContent.secondary_label" class="mt-1 block w-full border border-gray-300 rounded-md py-1.5 px-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase">Secondary link</label>
                            <input type="text" v-model="draftContent.secondary_link" class="mt-1 block w-full border border-gray-300 rounded-md py-1.5 px-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Video -->
                <div v-else-if="section.type === 'video'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Headline</label>
                        <input type="text" v-model="draftContent.headline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Subheadline</label>
                        <input type="text" v-model="draftContent.subheadline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Embed URL (YouTube / Vimeo)</label>
                        <input type="text" v-model="draftContent.embed_url" placeholder="https://youtu.be/..." class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to upload a video asset instead.</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Uploaded Video</label>
                        <div v-if="draftContent.video" class="relative mb-2">
                            <div class="bg-gray-900 text-white text-xs p-2 rounded">{{ draftContent.video.file_name || draftContent.video.url }}</div>
                            <button @click="draftContent.video = null" class="absolute top-1 right-1 bg-red-600 text-white p-1 rounded-full text-xs">Remove</button>
                        </div>
                        <button @click="openMediaPicker('video')" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Select Video</button>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Poster Image</label>
                        <div v-if="draftContent.poster" class="relative mb-2">
                            <img :src="draftContent.poster.url" class="h-20 w-full object-cover rounded">
                            <button @click="draftContent.poster = null" class="absolute top-1 right-1 bg-red-600 text-white p-1 rounded-full text-xs">Remove</button>
                        </div>
                        <button @click="openMediaPicker('poster')" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Select Poster</button>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Caption</label>
                        <input type="text" v-model="draftContent.caption" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                </div>

                <!-- Feature Grid -->
                <div v-else-if="section.type === 'feature_grid'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Eyebrow</label>
                        <input type="text" v-model="draftContent.eyebrow" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Headline</label>
                        <input type="text" v-model="draftContent.headline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Subheadline</label>
                        <textarea v-model="draftContent.subheadline" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm"></textarea>
                    </div>
                    <div class="space-y-4">
                        <div v-for="(item, idx) in draftContent.items" :key="idx" class="p-4 border border-gray-100 rounded-md bg-gray-50 relative">
                            <button @click="draftContent.items.splice(idx, 1)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">&times;</button>
                            <div class="space-y-2">
                                <input type="text" v-model="item.title" placeholder="Feature title" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                <textarea v-model="item.description" placeholder="Short description" rows="2" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm"></textarea>
                                <div class="flex items-center space-x-2">
                                    <div v-if="item.icon" class="h-8 w-8 rounded bg-white border flex items-center justify-center">
                                        <img :src="item.icon.url" class="h-6 w-6 object-contain">
                                    </div>
                                    <button @click="openMediaPicker('items_item_icon', idx)" class="text-xs text-indigo-600 hover:text-indigo-900">
                                        {{ item.icon ? 'Change icon' : '+ Icon' }}
                                    </button>
                                    <button v-if="item.icon" @click="item.icon = null" class="text-xs text-red-600 hover:text-red-900">Remove</button>
                                </div>
                            </div>
                        </div>
                        <button @click="draftContent.items.push({ title: 'New feature', description: '', icon: null })" class="w-full py-2 border border-dashed border-indigo-300 rounded text-sm text-indigo-600 hover:bg-indigo-50">+ Add feature</button>
                    </div>
                </div>

                <!-- Cards -->
                <div v-else-if="section.type === 'cards'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Headline</label>
                        <input type="text" v-model="draftContent.headline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Subheadline</label>
                        <input type="text" v-model="draftContent.subheadline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="space-y-4">
                        <div v-for="(card, idx) in draftContent.items" :key="idx" class="p-4 border border-gray-100 rounded-md bg-gray-50 relative">
                            <button @click="draftContent.items.splice(idx, 1)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">&times;</button>
                            <div class="space-y-2">
                                <input type="text" v-model="card.title" placeholder="Card title" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                <textarea v-model="card.description" placeholder="Description" rows="2" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm"></textarea>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" v-model="card.link_label" placeholder="Link label" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                    <input type="text" v-model="card.link" placeholder="URL" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div v-if="card.image" class="h-10 w-16 rounded bg-white border overflow-hidden">
                                        <img :src="card.image.url" class="h-full w-full object-cover">
                                    </div>
                                    <button @click="openMediaPicker('items_item_image', idx)" class="text-xs text-indigo-600 hover:text-indigo-900">
                                        {{ card.image ? 'Change image' : '+ Image' }}
                                    </button>
                                    <button v-if="card.image" @click="card.image = null" class="text-xs text-red-600 hover:text-red-900">Remove</button>
                                </div>
                            </div>
                        </div>
                        <button @click="draftContent.items.push({ title: 'New card', description: '', link: '', link_label: '', image: null })" class="w-full py-2 border border-dashed border-indigo-300 rounded text-sm text-indigo-600 hover:bg-indigo-50">+ Add card</button>
                    </div>
                </div>

                <!-- Testimonials -->
                <div v-else-if="section.type === 'testimonials'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Headline</label>
                        <input type="text" v-model="draftContent.headline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Subheadline</label>
                        <input type="text" v-model="draftContent.subheadline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="space-y-4">
                        <div v-for="(item, idx) in draftContent.items" :key="idx" class="p-4 border border-gray-100 rounded-md bg-gray-50 relative">
                            <button @click="draftContent.items.splice(idx, 1)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">&times;</button>
                            <div class="space-y-2">
                                <textarea v-model="item.quote" placeholder="Quote" rows="3" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm"></textarea>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" v-model="item.name" placeholder="Name" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                    <input type="text" v-model="item.role" placeholder="Role / Company" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div v-if="item.avatar" class="h-10 w-10 rounded-full bg-white border overflow-hidden">
                                        <img :src="item.avatar.url" class="h-full w-full object-cover">
                                    </div>
                                    <button @click="openMediaPicker('items_item_avatar', idx)" class="text-xs text-indigo-600 hover:text-indigo-900">
                                        {{ item.avatar ? 'Change avatar' : '+ Avatar' }}
                                    </button>
                                    <button v-if="item.avatar" @click="item.avatar = null" class="text-xs text-red-600 hover:text-red-900">Remove</button>
                                </div>
                            </div>
                        </div>
                        <button @click="draftContent.items.push({ quote: '', name: '', role: '', avatar: null })" class="w-full py-2 border border-dashed border-indigo-300 rounded text-sm text-indigo-600 hover:bg-indigo-50">+ Add testimonial</button>
                    </div>
                </div>

                <!-- Stats -->
                <div v-else-if="section.type === 'stats'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Headline</label>
                        <input type="text" v-model="draftContent.headline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Subheadline</label>
                        <input type="text" v-model="draftContent.subheadline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="space-y-3">
                        <div v-for="(stat, idx) in draftContent.items" :key="idx" class="p-3 border border-gray-100 rounded-md bg-gray-50 relative">
                            <button @click="draftContent.items.splice(idx, 1)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">&times;</button>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" v-model="stat.label" placeholder="Label (e.g. Customers)" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                <input type="number" v-model.number="stat.value" placeholder="Value (e.g. 1000)" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                <input type="text" v-model="stat.prefix" placeholder="Prefix (e.g. $)" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                <input type="text" v-model="stat.suffix" placeholder="Suffix (e.g. +)" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                            </div>
                        </div>
                        <button @click="draftContent.items.push({ label: '', value: 0, prefix: '', suffix: '' })" class="w-full py-2 border border-dashed border-indigo-300 rounded text-sm text-indigo-600 hover:bg-indigo-50">+ Add stat</button>
                    </div>
                </div>

                <!-- Carousel -->
                <div v-else-if="section.type === 'carousel'">
                    <div class="mb-4 p-3 bg-gray-50 rounded border border-gray-200">
                        <label class="inline-flex items-center text-sm text-gray-700">
                            <input type="checkbox" v-model="draftContent.autoplay" class="rounded border-gray-300 text-indigo-600 mr-2">
                            Autoplay
                        </label>
                        <div v-if="draftContent.autoplay" class="mt-2">
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Interval (ms)</label>
                            <input type="number" min="1000" step="500" v-model.number="draftContent.interval" class="block w-full border border-gray-300 rounded-md py-1.5 px-2 text-sm">
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div v-for="(slide, idx) in draftContent.items" :key="idx" class="p-4 border border-gray-100 rounded-md bg-gray-50 relative">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-semibold text-gray-500 uppercase">Slide {{ idx + 1 }}</span>
                                <button @click="draftContent.items.splice(idx, 1)" class="text-gray-400 hover:text-red-500">&times;</button>
                            </div>
                            <div class="space-y-2">
                                <input type="text" v-model="slide.headline" placeholder="Headline" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                <textarea v-model="slide.text" placeholder="Caption" rows="2" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm"></textarea>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" v-model="slide.cta_text" placeholder="CTA label" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                    <input type="text" v-model="slide.cta_link" placeholder="CTA URL" class="block w-full border border-gray-200 rounded-md py-1.5 px-2 text-sm">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div v-if="slide.image" class="h-12 w-20 rounded bg-white border overflow-hidden">
                                        <img :src="slide.image.url" class="h-full w-full object-cover">
                                    </div>
                                    <button @click="openMediaPicker('items_item_image', idx)" class="text-xs text-indigo-600 hover:text-indigo-900">
                                        {{ slide.image ? 'Change image' : '+ Image' }}
                                    </button>
                                    <button v-if="slide.image" @click="slide.image = null" class="text-xs text-red-600 hover:text-red-900">Remove</button>
                                </div>
                            </div>
                        </div>
                        <button @click="draftContent.items.push({ image: null, headline: 'New slide', text: '', cta_text: '', cta_link: '' })" class="w-full py-2 border border-dashed border-indigo-300 rounded text-sm text-indigo-600 hover:bg-indigo-50">+ Add slide</button>
                    </div>
                </div>

                <!-- Promo Banner -->
                <div v-else-if="section.type === 'promo_banner'">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Short label (mobile)</label>
                        <input type="text" v-model="draftContent.label" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Message</label>
                        <input type="text" v-model="draftContent.message" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <input type="text" v-model="draftContent.link_label" placeholder="Link label" class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm">
                        <input type="text" v-model="draftContent.link" placeholder="URL" class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Background color</label>
                        <input type="color" v-model="draftContent.background_color" class="mt-1 block w-24 h-9 border border-gray-300 rounded">
                    </div>
                    <label class="inline-flex items-center text-sm text-gray-700">
                        <input type="checkbox" v-model="draftContent.dismissible" class="rounded border-gray-300 text-indigo-600 mr-2">
                        Dismissible
                    </label>
                </div>

                <!-- Fallback info -->
                <div v-else class="text-sm text-gray-500 italic p-10 text-center">
                    No editor form required for type: <strong>{{ section.type }}</strong>
                </div>
            </div>
        </aside>

        <!-- Preview Area (Right) -->
        <main class="flex-1 bg-gray-100 overflow-y-auto w-full relative flex flex-col">
            <div class="bg-gray-200 border-b border-gray-300 px-4 py-2 text-xs text-gray-500 font-medium tracking-wider flex justify-center">
                {{ editMode === 'inline' ? 'Inline Edit — click any text or image to modify it' : 'Live Preview' }}
            </div>

            <!-- Validation errors banner -->
            <div v-if="validationErrors.length" class="bg-red-50 border-b border-red-200 px-4 py-3">
                <div class="max-w-4xl mx-auto flex items-start gap-3">
                    <svg class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-red-800">Please fix these issues before saving:</p>
                        <ul class="mt-1 list-disc list-inside text-sm text-red-700 space-y-0.5">
                            <li v-for="(err, i) in validationErrors" :key="i">{{ err }}</li>
                        </ul>
                    </div>
                    <button type="button" @click="validationErrors = []" class="text-red-400 hover:text-red-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto w-full">
                <!-- Wrapper acting like screen -->
                <div class="bg-white shadow w-full min-h-full relative">
                    <FrontendRenderer
                      v-if="section && draftContent"
                      :sections="[{ id: section.id, type: section.type, content: draftContent }]"
                      :editable="editMode === 'inline'"
                      @update:content="onInlineContentUpdate"
                      @pick-asset="onInlinePickAsset"
                    />
                </div>
            </div>
        </main>
    </div>

    <AssetPickerModal :is-open="mediaPickerOpen" :type="pickerType" @close="closePicker" @select="handleMediaSelect" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../axios';
import AssetPickerModal from '../components/AssetPickerModal.vue';
import FrontendRenderer from '../components/FrontendRenderer.vue';
import { validateSectionContent } from '../utils/sectionValidation';
import { useToast } from '../stores/toast';
import { useConfirm } from '../stores/confirm';

const toast = useToast();
const confirmDialog = useConfirm();

const route = useRoute();
const router = useRouter();
const sectionId = route.params.id;

const section = ref(null);
const draftContent = ref({});
const versions = ref([]);

const loading = ref(true);
const saving = ref(false);
const showVersions = ref(false);
const editMode = ref('inline'); // 'inline' | 'form'
const validationErrors = ref([]);

const mediaPickerOpen = ref(false);
const activeMediaField = ref('');
const activeMediaIndex = ref(null);
const activeMediaPath = ref(''); // full inline-mode path like "items[2].icon"
const pickerType = ref(null); // 'image' | 'video' | null, filters the picker

// Decide which asset type filter to apply to the media picker based on the
// field path being written. Anything matching /video/i gets 'video'; the
// embed_url text field doesn't use the picker at all.
const pickerTypeForField = (field) => {
    if (!field) return null;
    const f = field.toLowerCase();
    if (f === 'video' || f.endsWith('.video')) return 'video';
    return 'image';
};

onMounted(async () => {
    await fetchSection();
    await fetchVersions();
});

const fetchSection = async () => {
    loading.value = true;
    try {
        const res = await axios.get(`/sections/${sectionId}`);
        section.value = res.data.data;
        // Parse draft content or set defaults based on type
        draftContent.value = section.value.draft_content || getDefaultContent(section.value.type);
    } catch (err) {
        console.error('Failed to load section');
    } finally {
        loading.value = false;
    }
};

const getDefaultContent = (type) => {
    if (type === 'hero') return { headline: '', text: '', bg_image: null };
    if (type === 'text') return { body: '' };
    if (type === 'image_text') return { headline: '', text: '', image: null, image_position: 'left' };
    if (type === 'gallery') return { headline: '', images: [] };
    if (type === 'carousel') return { autoplay: true, interval: 5000, items: [{ image: null, headline: 'First slide', text: '', cta_text: '', cta_link: '' }] };
    if (type === 'tabs') return { headline: '', items: [{ title: 'Tab 1', content: 'Tab 1 content' }] };
    if (type === 'accordion') return { headline: '', items: [{ title: 'Question 1', content: 'Answer 1 content' }] };
    if (type === 'cta') return { headline: '', subheadline: '', primary_label: '', primary_link: '', secondary_label: '', secondary_link: '' };
    if (type === 'video') return { headline: '', subheadline: '', embed_url: '', video: null, poster: null, caption: '' };
    if (type === 'feature_grid') return { eyebrow: '', headline: '', subheadline: '', items: [{ title: 'Feature', description: '', icon: null }] };
    if (type === 'cards') return { headline: '', subheadline: '', items: [{ title: 'Card', description: '', link: '', link_label: '', image: null }] };
    if (type === 'testimonials') return { headline: '', subheadline: '', items: [{ quote: '', name: '', role: '', avatar: null }] };
    if (type === 'stats') return { headline: '', subheadline: '', items: [{ label: '', value: 0, prefix: '', suffix: '' }] };
    if (type === 'promo_banner') return { label: '', message: '', link: '', link_label: '', background_color: '#4f46e5', dismissible: true };
    return {};
};

const fetchVersions = async () => {
    try {
        const res = await axios.get(`/sections/${sectionId}/history`);
        versions.value = res.data.data;
    } catch (err) {
        console.error('Failed to fetch history');
    }
};

const saveDraft = async () => {
    // Client-side validation first — fast feedback, no round-trip.
    const errors = validateSectionContent(section.value?.type, draftContent.value);
    if (errors.length) {
        validationErrors.value = errors;
        // Scroll to top of preview so the banner is visible
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }
    validationErrors.value = [];

    saving.value = true;
    try {
        await axios.put(`/sections/${sectionId}`, {
            draft_content: draftContent.value
        });
        await fetchSection();
        await fetchVersions();
        toast.success('Draft saved.', {
            title: 'Not yet on the public site',
        });
        // Friendly reminder that Save Draft does NOT publish to the public site.
        const goPublish = await confirmDialog.ask({
            title: 'Go to Publish?',
            message: 'Your changes are saved as a draft, but they\'re not visible on the public site yet. Open the Publish screen now?',
            confirmLabel: 'Go to Publish',
        });
        if (goPublish) {
            await router.push({ name: 'publish' });
        }
    } catch (err) {
        // Server-side validation errors come back as { errors: [...] }
        const apiErrors = err?.response?.data?.errors;
        if (Array.isArray(apiErrors)) {
            validationErrors.value = apiErrors;
        } else if (apiErrors && typeof apiErrors === 'object') {
            validationErrors.value = Object.values(apiErrors).flat();
        } else {
            toast.error('Failed to save');
        }
    } finally {
        saving.value = false;
    }
};

const rollback = async (versionId) => {
    const ok = await confirmDialog.ask({
        title: 'Restore this version?',
        message: 'Any unsaved changes will be lost.',
        confirmLabel: 'Restore',
    });
    if (!ok) return;
    try {
        await axios.post(`/sections/${sectionId}/history/${versionId}/rollback`);
        await fetchSection();
        showVersions.value = false;
        toast.success('Section restored to selected version.');
    } catch (err) {
        toast.error('Rollback failed');
    }
};

const openMediaPicker = (fieldName, index = null) => {
    activeMediaField.value = fieldName;
    activeMediaIndex.value = index;
    pickerType.value = pickerTypeForField(fieldName);
    mediaPickerOpen.value = true;
};

const closePicker = () => {
    mediaPickerOpen.value = false;
    pickerType.value = null;
    activeMediaPath.value = '';
    activeMediaField.value = '';
};

const handleMediaSelect = (asset) => {
    // Inline-mode path has priority when it's set.
    if (activeMediaPath.value) {
        applyInlinePath(activeMediaPath.value, asset);
        activeMediaPath.value = '';
        activeMediaIndex.value = null;
        return;
    }

    const field = activeMediaField.value;
    if (field === 'images_array') {
        if (!draftContent.value.images) draftContent.value.images = [];
        draftContent.value.images.push(asset);
    } else if (field?.startsWith('items_item_')) {
        const subField = field.replace('items_item_', '');
        const idx = activeMediaIndex.value;
        if (draftContent.value.items && draftContent.value.items[idx] !== undefined) {
            draftContent.value.items[idx][subField] = asset;
        }
    } else if (field) {
        draftContent.value[field] = asset;
    }
    activeMediaIndex.value = null;
};

// --- Inline edit handlers -------------------------------------------------

const onInlineContentUpdate = ({ content }) => {
    // The block emits the full patched content object for its section.
    draftContent.value = content;
};

const onInlinePickAsset = ({ field }) => {
    // field examples: "bg_image", "items[2].icon", "images[]", "images[3]"
    activeMediaPath.value = field;
    pickerType.value = pickerTypeForField(field);
    mediaPickerOpen.value = true;
};

// Apply an asset to a dot/bracket path on draftContent.
// Supported shapes: "foo", "foo.bar", "items[0].icon", "images[]" (append), "images[2]"
const applyInlinePath = (path, asset) => {
    const appendMatch = path.match(/^(.+)\[\]$/);
    if (appendMatch) {
        const arrPath = appendMatch[1];
        const arr = [...(readPath(arrPath) || [])];
        arr.push(asset);
        writePath(arrPath, arr);
        return;
    }
    writePath(path, asset);
};

const readPath = (path) => {
    const tokens = tokenize(path);
    let cur = draftContent.value;
    for (const t of tokens) {
        if (cur == null) return undefined;
        cur = cur[t];
    }
    return cur;
};

const writePath = (path, value) => {
    const tokens = tokenize(path);
    if (tokens.length === 0) return;
    // Always replace draftContent with a fresh shallow-cloned tree along the path
    // to keep Vue reactivity predictable.
    const root = Array.isArray(draftContent.value) ? [...draftContent.value] : { ...draftContent.value };
    let cur = root;
    for (let i = 0; i < tokens.length - 1; i++) {
        const key = tokens[i];
        const next = cur[key];
        const clone = Array.isArray(next) ? [...next] : { ...(next || {}) };
        cur[key] = clone;
        cur = clone;
    }
    cur[tokens[tokens.length - 1]] = value;
    draftContent.value = root;
};

// "items[2].icon" -> ["items", 2, "icon"]
const tokenize = (path) => {
    const out = [];
    const re = /([^.[\]]+)|\[(\d+)\]/g;
    let m;
    while ((m = re.exec(path)) !== null) {
        if (m[1] !== undefined) out.push(m[1]);
        else out.push(Number(m[2]));
    }
    return out;
};
</script>
