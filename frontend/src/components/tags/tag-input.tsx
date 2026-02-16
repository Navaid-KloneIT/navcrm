'use client';

import React, { useEffect, useState, useRef } from 'react';
import { X } from 'lucide-react';
import { tagsApi } from '@/lib/api/tags';
import type { Tag } from '@/types';
import { cn } from '@/lib/utils/cn';

interface TagInputProps {
  selectedTags: Tag[];
  onChange: (tags: Tag[]) => void;
}

function TagInput({ selectedTags, onChange }: TagInputProps) {
  const [allTags, setAllTags] = useState<Tag[]>([]);
  const [search, setSearch] = useState('');
  const [isOpen, setIsOpen] = useState(false);
  const [creating, setCreating] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);
  const dropdownRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const fetchTags = async () => {
      try {
        const response = await tagsApi.list();
        setAllTags(response.data.data);
      } catch {
        // Silently handle
      }
    };
    fetchTags();
  }, []);

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (
        dropdownRef.current &&
        !dropdownRef.current.contains(event.target as Node)
      ) {
        setIsOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const filteredTags = allTags.filter(
    (tag) =>
      !selectedTags.some((st) => st.id === tag.id) &&
      tag.name.toLowerCase().includes(search.toLowerCase())
  );

  const handleSelect = (tag: Tag) => {
    onChange([...selectedTags, tag]);
    setSearch('');
    setIsOpen(false);
    inputRef.current?.focus();
  };

  const handleRemove = (tagId: number) => {
    onChange(selectedTags.filter((t) => t.id !== tagId));
  };

  const handleCreate = async () => {
    if (!search.trim() || creating) return;
    setCreating(true);
    try {
      const response = await tagsApi.create({ name: search.trim() });
      const newTag = response.data.data;
      setAllTags((prev) => [...prev, newTag]);
      onChange([...selectedTags, newTag]);
      setSearch('');
      setIsOpen(false);
    } catch {
      // Silently handle
    } finally {
      setCreating(false);
    }
  };

  return (
    <div className="w-full" ref={dropdownRef}>
      <label className="mb-1 block text-sm font-medium text-gray-700">
        Tags
      </label>
      <div className="rounded-md border border-gray-300 bg-white px-2 py-1.5">
        <div className="flex flex-wrap items-center gap-1.5">
          {selectedTags.map((tag) => (
            <span
              key={tag.id}
              className="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
              style={
                tag.color
                  ? { backgroundColor: `${tag.color}20`, color: tag.color }
                  : { backgroundColor: '#e5e7eb', color: '#374151' }
              }
            >
              {tag.name}
              <button
                type="button"
                onClick={() => handleRemove(tag.id)}
                className="ml-0.5 hover:opacity-75"
              >
                <X className="h-3 w-3" />
              </button>
            </span>
          ))}
          <input
            ref={inputRef}
            type="text"
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setIsOpen(true);
            }}
            onFocus={() => setIsOpen(true)}
            placeholder={selectedTags.length === 0 ? 'Search or create tags...' : ''}
            className="min-w-[120px] flex-1 border-0 bg-transparent px-1 py-0.5 text-sm outline-none placeholder:text-gray-400"
          />
        </div>
      </div>

      {isOpen && (search || filteredTags.length > 0) && (
        <div className="absolute z-10 mt-1 max-h-48 w-64 overflow-y-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg">
          {filteredTags.map((tag) => (
            <button
              key={tag.id}
              type="button"
              onClick={() => handleSelect(tag)}
              className="flex w-full items-center gap-2 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100"
            >
              {tag.color && (
                <span
                  className="h-3 w-3 rounded-full"
                  style={{ backgroundColor: tag.color }}
                />
              )}
              {tag.name}
            </button>
          ))}
          {search && !filteredTags.some((t) => t.name.toLowerCase() === search.toLowerCase()) && (
            <button
              type="button"
              onClick={handleCreate}
              disabled={creating}
              className={cn(
                'flex w-full items-center gap-2 px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50',
                creating && 'opacity-50'
              )}
            >
              {creating ? 'Creating...' : `Create "${search}"`}
            </button>
          )}
          {filteredTags.length === 0 && !search && (
            <p className="px-3 py-2 text-xs text-gray-400">No tags available</p>
          )}
        </div>
      )}
    </div>
  );
}

export { TagInput };
